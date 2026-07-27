<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class MeasurementTypesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('measurement_types');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('CatalogProducts');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->scalar('slug')->maxLength('slug', 60)->requirePresence('slug', 'create')->notEmptyString('slug')
            ->scalar('name')->maxLength('name', 100)->requirePresence('name', 'create')->notEmptyString('name')
            ->allowEmptyArray('units')
            ->integer('sort_order')
            ->boolean('active');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }
}
