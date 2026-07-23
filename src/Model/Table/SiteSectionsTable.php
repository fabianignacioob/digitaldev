<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class SiteSectionsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('site_sections');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Sites');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('site_id')->notEmptyString('site_id')
            ->scalar('section_key')->maxLength('section_key', 80)->requirePresence('section_key', 'create')->notEmptyString('section_key')
            ->scalar('title')->maxLength('title', 180)->allowEmptyString('title')
            ->scalar('subtitle')->maxLength('subtitle', 220)->allowEmptyString('subtitle')
            ->allowEmptyString('content')
            ->integer('sort_order')
            ->boolean('visible');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['site_id'], 'Sites'), ['errorField' => 'site_id']);

        return $rules;
    }
}
