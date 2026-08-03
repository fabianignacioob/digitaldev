<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\DomainProvisioningService;
use App\Service\OperationalProcessRunService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactory;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

class DomainsCommand extends Command
{
    public function __construct(?CommandFactory $factory = null) { parent::__construct($factory); }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription('Opera el provisionamiento asíncrono de dominios propios.')
            ->addArgument('action', ['choices' => ['provision'], 'required' => true])
            ->addOption('dry-run', ['boolean' => true, 'default' => false])
            ->addOption('limit', ['default' => 25]);
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $dryRun = (bool)$args->getOption('dry-run');
        $runService = new OperationalProcessRunService();
        $run = $runService->start('domains.provision', ['dry_run' => $dryRun]);
        try {
            $result = (new DomainProvisioningService())->provisionReadyDomains((int)$args->getOption('limit'), $dryRun);
            $message = sprintf('Resumen: procesados=%d omitidos=%d errores=%d dry_run=%s', $result['processed'], $result['skipped'], $result['errors'], $dryRun ? 'sí' : 'no');
            $runService->finish($run, $result['errors'] ? 'failed' : 'success', $result['processed'], $result['skipped'], $result['errors'], $message);
            $io->out($message);
            return $result['errors'] ? self::CODE_ERROR : self::CODE_SUCCESS;
        } catch (\Throwable $exception) {
            $runService->finish($run, 'failed', 0, 0, 1, 'El provisionamiento terminó con un error. Revisa los logs internos.');
            throw $exception;
        }
    }
}
