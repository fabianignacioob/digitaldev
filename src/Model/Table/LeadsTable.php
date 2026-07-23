<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class LeadsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('leads');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->belongsTo('Sites');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('site_id')->notEmptyString('site_id')
            ->scalar('name')->maxLength('name', 120)->requirePresence('name', 'create')->notEmptyString('name')
            ->email('email')->allowEmptyString('email')
            ->scalar('phone')->maxLength('phone', 40)->allowEmptyString('phone')
            ->allowEmptyString('message')
            ->scalar('source')->maxLength('source', 80);
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['site_id'], 'Sites'), ['errorField' => 'site_id']);

        return $rules;
    }
}
