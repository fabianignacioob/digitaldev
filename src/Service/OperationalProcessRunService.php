<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\ORM\Table;

class OperationalProcessRunService
{
    public function start(string $processName, array $metadata = []): object
    {
        $run = $this->runs()->newEntity([
            'process_name' => $processName,
            'started_at' => DateTime::now(),
            'status' => 'running',
            // SQLite's test schema represents JSON as text; serialize explicitly so
            // process tracking behaves identically in test and PostgreSQL.
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
        ]);
        $this->runs()->saveOrFail($run);

        return $run;
    }

    public function finish(
        object $run,
        string $status,
        int $processed,
        int $skipped,
        int $errors,
        string $message,
    ): object {
        $run = $this->runs()->get((int)$run->id);
        $run->finished_at = DateTime::now();
        $run->status = $status;
        $run->processed_count = max(0, $processed);
        $run->skipped_count = max(0, $skipped);
        $run->error_count = max(0, $errors);
        $run->message = mb_substr(trim($message), 0, 500);
        $this->runs()->saveOrFail($run);

        return $run;
    }

    public function latestByProcess(array $processNames): array
    {
        $latest = [];
        foreach ($processNames as $processName) {
            $latest[$processName] = $this->runs()->find()
                ->where(['process_name' => $processName])
                ->orderByDesc('started_at')
                ->first();
        }

        return $latest;
    }

    private function runs(): Table
    {
        return FactoryLocator::get('Table')->get('OperationalProcessRuns');
    }
}
