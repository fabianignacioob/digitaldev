<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ThemesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('themes');
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
            ->scalar('primary_color')->maxLength('primary_color', 20)
            ->scalar('secondary_color')->maxLength('secondary_color', 20)
            ->scalar('background_color')->maxLength('background_color', 20)
            ->boolean('active');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }
}
