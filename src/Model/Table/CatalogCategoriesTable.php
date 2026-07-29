<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class CatalogCategoriesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('catalog_categories');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Sites');
        $this->hasMany('CatalogProducts', [
            'sort' => ['CatalogProducts.sort_order' => 'ASC', 'CatalogProducts.name' => 'ASC'],
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('site_id')->notEmptyString('site_id')
            ->scalar('name')->maxLength('name', 120)->requirePresence('name', 'create')->notEmptyString('name')
            ->integer('sort_order');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['site_id'], 'Sites'), ['errorField' => 'site_id']);
        $rules->add($rules->isUnique(['site_id', 'name']), ['errorField' => 'name']);

        return $rules;
    }
}
