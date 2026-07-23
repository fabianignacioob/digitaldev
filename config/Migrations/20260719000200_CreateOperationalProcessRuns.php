<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateOperationalProcessRuns extends BaseMigration
{
    public function change(): void
    {
        $this->table('operational_process_runs')
            ->addColumn('process_name', 'string', ['limit' => 80])
            ->addColumn('started_at', 'datetime')
            ->addColumn('finished_at', 'datetime', ['null' => true])
            ->addColumn('status', 'string', ['limit' => 20])
            ->addColumn('processed_count', 'integer', ['default' => 0])
            ->addColumn('skipped_count', 'integer', ['default' => 0])
            ->addColumn('error_count', 'integer', ['default' => 0])
            ->addColumn('message', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('metadata', 'json', ['default' => '{}'])
            ->addTimestamps('created', 'modified')
            ->addIndex(['process_name', 'started_at'], ['name' => 'operational_process_runs_name_started'])
            ->addIndex(['status', 'started_at'], ['name' => 'operational_process_runs_status_started'])
            ->create();
    }
}
