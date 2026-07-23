<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class OperationalProcessRunsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('operational_process_runs');
        $this->setDisplayField('process_name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->scalar('process_name')->maxLength('process_name', 80)->notEmptyString('process_name')
            ->dateTime('started_at')->notEmptyDateTime('started_at')
            ->dateTime('finished_at')->allowEmptyDateTime('finished_at')
            ->scalar('status')->maxLength('status', 20)->inList('status', ['running', 'success', 'failed'])
            ->integer('processed_count')->greaterThanOrEqual('processed_count', 0)
            ->integer('skipped_count')->greaterThanOrEqual('skipped_count', 0)
            ->integer('error_count')->greaterThanOrEqual('error_count', 0)
            ->scalar('message')->maxLength('message', 500)->allowEmptyString('message');
    }
}
