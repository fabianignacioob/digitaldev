<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Sites');
        $this->hasMany('AuditLogs');
        $this->hasMany('Subscriptions');
        $this->hasMany('Payments');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->scalar('name')->maxLength('name', 120)->requirePresence('name', 'create')->notEmptyString('name')
            ->email('email')->requirePresence('email', 'create')->notEmptyString('email')
            ->scalar('password')->maxLength('password', 255)->requirePresence('password', 'create')->notEmptyString('password')
            // `customer` remains valid only for records created before the role migration.
            ->scalar('role')->maxLength('role', 30)->inList('role', ['user', 'admin', 'superadmin', 'customer'])
            ->boolean('active')
            ->boolean('email_verified')
            ->scalar('verification_code_hash')->maxLength('verification_code_hash', 255)->allowEmptyString('verification_code_hash')
            ->dateTime('verification_expires')->allowEmptyDateTime('verification_expires')
            ->dateTime('verification_sent_at')->allowEmptyDateTime('verification_sent_at')
            ->dateTime('trial_used_at')->allowEmptyDateTime('trial_used_at');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);

        return $rules;
    }
}
