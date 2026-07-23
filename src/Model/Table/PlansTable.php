<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class PlansTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('plans');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->scalar('name')->maxLength('name', 80)->requirePresence('name', 'create')->notEmptyString('name')
            ->scalar('slug')->maxLength('slug', 40)->requirePresence('slug', 'create')->notEmptyString('slug')
            ->integer('monthly_price')->notEmptyString('monthly_price')
            ->integer('max_sites')->notEmptyString('max_sites')
            ->integer('max_published')->notEmptyString('max_published')
            ->allowEmptyArray('capabilities')
            ->integer('sort_order')
            ->boolean('active');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }
}
