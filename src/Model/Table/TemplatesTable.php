<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class TemplatesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('templates');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('Sites');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->scalar('name')->maxLength('name', 120)->requirePresence('name', 'create')->notEmptyString('name')
            ->scalar('slug')->maxLength('slug', 120)->requirePresence('slug', 'create')->notEmptyString('slug')
            ->boolean('active');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }
}
