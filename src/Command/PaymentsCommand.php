<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\PaymentService;
use App\Service\PaymentReconciliationService;
use App\Service\OperationalProcessRunService;
use App\Service\WebpayPlusGateway;
use App\Service\WebpayPlusGatewayInterface;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactory;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;

class PaymentsCommand extends Command
{
    public function __construct(?CommandFactory $factory = null)
    {
        parent::__construct($factory);
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser
            ->setDescription('Opera conciliaciones y pruebas controladas de Webpay Plus.')
            ->addArgument('action', [
                'help' => 'Acción a ejecutar.',
                'choices' => ['reconcile', 'create_integration_test'],
                'required' => true,
            ])
            ->addOption('dry-run', [
                'help' => 'Muestra los cambios sin modificar datos.',
                'boolean' => true,
                'default' => false,
            ])
            ->addOption('user-id', [
                'help' => 'ID del administrador que ejecuta la prueba de integración.',
            ]);
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $action = (string)$args->getArgument('action');
        $dryRun = (bool)$args->getOption('dry-run');
        $runs = new OperationalProcessRunService();
        $run = $runs->start('payments.' . $action, ['dry_run' => $dryRun]);

        try {
            $result = match ($action) {
                'reconcile' => $this->reconcile($dryRun, $io),
                'create_integration_test' => $this->createIntegrationTest((int)$args->getOption('user-id'), $dryRun, $io),
                default => ['code' => self::CODE_ERROR, 'processed' => 0, 'skipped' => 0, 'errors' => 1, 'message' => 'Acción no reconocida.'],
            };
            $runs->finish(
                $run,
                $result['code'] === self::CODE_SUCCESS ? 'success' : 'failed',
                $result['processed'],
                $result['skipped'],
                $result['errors'],
                $result['message'],
            );

            return $result['code'];
        } catch (\Throwable $exception) {
            $runs->finish($run, 'failed', 0, 0, 1, 'El proceso terminó con un error. Revisa los logs internos.');
            throw $exception;
        }
    }

    /** @return array{code:int, processed:int, skipped:int, errors:int, message:string} */
    private function reconcile(bool $dryRun, ConsoleIo $io): array
    {
        $processed = 0;
        $skipped = 0;
        $errors = 0;
        $gateway = $this->gateway();
        $service = new PaymentReconciliationService($gateway);
        $paymentService = new PaymentService();
        $payments = $this->payments()->find()
            ->where([
                'provider' => PaymentService::PROVIDER,
                'status IN' => [PaymentService::STATUS_PENDING, PaymentService::STATUS_AUTHORIZED],
                'gateway_token IS NOT' => null,
            ])
            ->orderByAsc('created');

        foreach ($payments as $payment) {
            try {
                $result = $service->reconcile($payment, $dryRun);
                $action = $result['action'];
                if ($action === 'omitido') {
                    $skipped++;
                    continue;
                }

                $io->out(sprintf('Pago #%d: %s', (int)$payment->id, $this->commandLabel($action)));
                $processed++;
            } catch (\Throwable) {
                $errors++;
                if (!$dryRun) {
                    $paymentService->recordGatewayFailure($payment, 'gateway_reconcile_failed');
                }
                $io->err(sprintf('Pago #%d: no se pudo consultar Webpay.', (int)$payment->id));
            }
        }

        $message = sprintf(
            'Resumen: procesados=%d omitidos=%d errores=%d dry_run=%s',
            $processed,
            $skipped,
            $errors,
            $dryRun ? 'sí' : 'no',
        );
        $io->out($message);

        return [
            'code' => $errors > 0 ? self::CODE_ERROR : self::CODE_SUCCESS,
            'processed' => $processed,
            'skipped' => $skipped,
            'errors' => $errors,
            'message' => $message,
        ];
    }

    /** @return array{code:int, processed:int, skipped:int, errors:int, message:string} */
    private function createIntegrationTest(int $userId, bool $dryRun, ConsoleIo $io): array
    {
        if ($dryRun) {
            $message = 'Dry-run: no se creó una orden de prueba Webpay.';
            $io->out($message);

            return ['code' => self::CODE_SUCCESS, 'processed' => 0, 'skipped' => 1, 'errors' => 0, 'message' => $message];
        }
        if ($userId < 1) {
            $message = 'Debes indicar --user-id con el ID de un administrador.';
            $io->err($message);

            return ['code' => self::CODE_ERROR, 'processed' => 0, 'skipped' => 0, 'errors' => 1, 'message' => $message];
        }

        $payment = null;
        $paymentService = new PaymentService();

        try {
            $payment = $paymentService->createIntegrationTestOrder($userId);
            $transaction = $this->gateway()->createTransaction(
                (string)$payment->buy_order,
                (string)$payment->session_id,
                (int)$payment->expected_amount,
            );
            $payment = $paymentService->recordGatewayTransaction($payment, $transaction);

            $io->out('Orden de prueba Webpay creada.');
            $io->out('Monto: $1 CLP');
            $io->out('Referencia: ' . (string)$payment->internal_reference);
            $io->out('URL: ' . (string)$transaction['url']);
            $io->out('token_ws: ' . (string)$transaction['token']);

            return [
                'code' => self::CODE_SUCCESS,
                'processed' => 1,
                'skipped' => 0,
                'errors' => 0,
                'message' => 'Orden de prueba Webpay creada.',
            ];
        } catch (\Throwable $exception) {
            if ($payment !== null) {
                $paymentService->markGatewaySetupFailed($payment);
            }
            $message = 'No se pudo crear la orden de prueba Webpay: ' . $exception->getMessage();
            $io->err($message);

            return ['code' => self::CODE_ERROR, 'processed' => 0, 'skipped' => 0, 'errors' => 1, 'message' => 'La orden de prueba no pudo crearse.'];
        }
    }

    private function gateway(): WebpayPlusGatewayInterface
    {
        $gateway = Configure::read('Payments.webpayGateway');

        return $gateway instanceof WebpayPlusGatewayInterface ? $gateway : new WebpayPlusGateway();
    }

    private function commandLabel(string $action): string
    {
        return match ($action) {
            'confirmado' => 'confirmar',
            'rechazado' => 'rechazar',
            'reversado' => 'reversar',
            'expirado' => 'expirar',
            default => $action,
        };
    }

    private function payments(): object
    {
        return FactoryLocator::get('Table')->get('Payments');
    }
}
