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
            ->setDescription('Concilia pagos pendientes de Webpay Plus.')
            ->addArgument('action', [
                'help' => 'Acción a ejecutar.',
                'choices' => ['reconcile'],
                'required' => true,
            ])
            ->addOption('dry-run', [
                'help' => 'Muestra los cambios sin modificar datos.',
                'boolean' => true,
                'default' => false,
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
