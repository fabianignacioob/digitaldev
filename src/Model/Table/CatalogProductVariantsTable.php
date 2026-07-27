<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class CatalogProductVariantsTable extends Table
{
    public const AVAILABILITIES = ['available', 'unavailable', 'coming_soon'];

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('catalog_product_variants');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('CatalogProducts');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('catalog_product_id')->notEmptyString('catalog_product_id')
            ->scalar('name')->maxLength('name', 120)->requirePresence('name', 'create')->notEmptyString('name')
            ->decimal('measurement_value')->greaterThanOrEqual('measurement_value', 0)->allowEmptyString('measurement_value')
            ->scalar('measurement_unit')->maxLength('measurement_unit', 30)->allowEmptyString('measurement_unit')
            ->decimal('price')->greaterThanOrEqual('price', 0)->allowEmptyString('price')
            ->scalar('availability')->inList('availability', self::AVAILABILITIES)->notEmptyString('availability')
            ->integer('sort_order');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['catalog_product_id'], 'CatalogProducts'), ['errorField' => 'catalog_product_id']);
        return $rules;
    }
}
