<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class CatalogProductsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('catalog_products');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Sites');
        $this->belongsTo('CatalogCategories');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('site_id')->notEmptyString('site_id')
            ->integer('catalog_category_id')->allowEmptyString('catalog_category_id')
            ->scalar('image_path')->maxLength('image_path', 255)->allowEmptyString('image_path')
            ->scalar('item_type')->maxLength('item_type', 30)->notEmptyString('item_type')
            ->scalar('name')->maxLength('name', 140)->requirePresence('name', 'create')->notEmptyString('name')
            ->scalar('description')->maxLength('description', 260)->allowEmptyString('description')
            ->decimal('price')->allowEmptyString('price')
            ->scalar('price_prefix')->maxLength('price_prefix', 40)->allowEmptyString('price_prefix')
            ->decimal('discount')->allowEmptyString('discount')
            ->scalar('duration')->maxLength('duration', 80)->allowEmptyString('duration')
            ->boolean('featured')
            ->boolean('active')
            ->integer('sort_order');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['site_id'], 'Sites'), ['errorField' => 'site_id']);
        $rules->add($rules->existsIn(['catalog_category_id'], 'CatalogCategories'), [
            'errorField' => 'catalog_category_id',
            'allowNullableNulls' => true,
        ]);

        return $rules;
    }
}
