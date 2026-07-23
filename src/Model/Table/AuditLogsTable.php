<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class AuditLogsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('audit_logs');
        $this->setDisplayField('action');
        $this->setPrimaryKey('id');
        $this->belongsTo('Users');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('user_id')->allowEmptyString('user_id')
            ->scalar('action')->maxLength('action', 120)->requirePresence('action', 'create')->notEmptyString('action')
            ->scalar('entity')->maxLength('entity', 120)->allowEmptyString('entity')
            ->integer('entity_id')->allowEmptyString('entity_id');
    }
}
