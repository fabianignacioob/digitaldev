<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\SubscriptionService;
use App\Service\OperationalProcessRunService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactory;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;

class SubscriptionsCommand extends Command
{
    private ?SubscriptionService $subscriptionService = null;

    public function __construct(?CommandFactory $factory = null)
    {
        parent::__construct($factory);
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Procesa vencimientos y recordatorios de suscripciones CatOps.')
            ->addArgument('action', [
                'help' => 'Acción a ejecutar.',
                'choices' => ['process_expirations', 'reminders'],
                'required' => true,
            ])
            ->addOption('dry-run', [
                'help' => 'Muestra lo que ocurriría sin modificar datos.',
                'boolean' => true,
                'default' => false,
            ]);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $action = (string)$args->getArgument('action');
        $dryRun = (bool)$args->getOption('dry-run');
        $runs = new OperationalProcessRunService();
        $run = $runs->start('subscriptions.' . $action, ['dry_run' => $dryRun]);

        try {
            $result = match ($action) {
                'process_expirations' => $this->processExpirations($dryRun, $io),
                'reminders' => $this->processReminders($io),
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
    private function processExpirations(bool $dryRun, ConsoleIo $io): array
    {
        $now = DateTime::now();
        $subscriptionService = $this->subscriptionService();
        $expiringUntil = DateTime::now()->addDays($subscriptionService->expiringWindowDays());
        $processed = 0;
        $skipped = 0;
        $errors = 0;

        $subscriptions = $this->subscriptions()->find()
            ->where([
                'status IN' => [
                    SubscriptionService::STATUS_ACTIVE,
                    SubscriptionService::STATUS_EXPIRING,
                    SubscriptionService::STATUS_GRACE,
                    SubscriptionService::STATUS_TRIAL_PENDING,
                ],
            ])
            ->orderByAsc('ends_at');

        foreach ($subscriptions as $subscription) {
            try {
                $status = (string)$subscription->status;
                $endsAt = $subscription->ends_at ? $this->asDateTime($subscription->ends_at) : null;
                $graceEndsAt = $subscription->grace_ends_at ? $this->asDateTime($subscription->grace_ends_at) : null;

                if ($status === SubscriptionService::STATUS_TRIAL_PENDING) {
                    $registrationEndsAt = $subscription->trial_registration_expires_at
                        ? $this->asDateTime($subscription->trial_registration_expires_at)
                        : null;
                    if ($registrationEndsAt && $registrationEndsAt < $now) {
                        $io->out(sprintf('Expirar prueba pendiente #%d', (int)$subscription->id));
                        if (!$dryRun) {
                            $subscriptionService->processExpiration($subscription);
                        }
                        $processed++;
                        continue;
                    }
                    $skipped++;
                    continue;
                }

                if ($status === SubscriptionService::STATUS_GRACE && $graceEndsAt && $graceEndsAt < $now) {
                    $io->out(sprintf('Expirar suscripción #%d', (int)$subscription->id));
                    if (!$dryRun) {
                        $subscriptionService->expire($subscription);
                    }
                    $processed++;
                    continue;
                }

                if (in_array($status, [SubscriptionService::STATUS_ACTIVE, SubscriptionService::STATUS_EXPIRING], true) && $endsAt && $endsAt < $now) {
                    $io->out(sprintf('Procesar vencimiento de suscripción #%d', (int)$subscription->id));
                    if (!$dryRun) {
                        $subscriptionService->processExpiration($subscription);
                    }
                    $processed++;
                    continue;
                }

                if ($status === SubscriptionService::STATUS_ACTIVE && $endsAt && $endsAt <= $expiringUntil) {
                    $io->out(sprintf('Marcar por vencer suscripción #%d', (int)$subscription->id));
                    if (!$dryRun) {
                        $subscriptionService->markExpiring($subscription);
                    }
                    $processed++;
                    continue;
                }

                $skipped++;
            } catch (\Throwable $exception) {
                $errors++;
                $io->err(sprintf('Error en suscripción #%d: %s', (int)$subscription->id, $exception->getMessage()));
            }
        }

        $message = sprintf(
            'Resumen: procesados=%d omitidos=%d errores=%d dry_run=%s',
            $processed,
            $skipped,
            $errors,
            $dryRun ? 'sí' : 'no'
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
    private function processReminders(ConsoleIo $io): array
    {
        $now = DateTime::now();
        $processed = 0;
        foreach ([7, 3, 1, 0] as $days) {
            $from = (clone $now)->addDays($days)->setTime(0, 0, 0);
            $to = (clone $now)->addDays($days)->setTime(23, 59, 59);
            $count = $this->subscriptions()->find()
                ->where([
                    'status IN' => [SubscriptionService::STATUS_ACTIVE, SubscriptionService::STATUS_EXPIRING],
                    'ends_at >=' => $from,
                    'ends_at <=' => $to,
                ])
                ->count();

            $label = $days === 0 ? 'vencen hoy' : 'vencen en ' . $days . ' días';
            $io->out(sprintf('%s: %d', $label, $count));
            $processed += $count;
        }

        return [
            'code' => self::CODE_SUCCESS,
            'processed' => $processed,
            'skipped' => 0,
            'errors' => 0,
            'message' => sprintf('Recordatorios detectados=%d.', $processed),
        ];
    }

    private function asDateTime(mixed $value): DateTime
    {
        return $value instanceof DateTime ? $value : new DateTime((string)$value);
    }

    private function subscriptions(): object
    {
        return FactoryLocator::get('Table')->get('Subscriptions');
    }

    private function subscriptionService(): SubscriptionService
    {
        $this->subscriptionService ??= new SubscriptionService();

        return $this->subscriptionService;
    }
}
