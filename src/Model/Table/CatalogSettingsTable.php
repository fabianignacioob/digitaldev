<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class CatalogSettingsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('catalog_settings');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Sites');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('site_id')->notEmptyString('site_id')
            ->scalar('background_type')->maxLength('background_type', 20)->notEmptyString('background_type')
            ->scalar('background_color')->maxLength('background_color', 20)->notEmptyString('background_color')
            ->scalar('background_image_path')->maxLength('background_image_path', 255)->allowEmptyString('background_image_path')
            ->scalar('background_preset')->maxLength('background_preset', 40)->allowEmptyString('background_preset')
            ->scalar('title_color')->maxLength('title_color', 20)->notEmptyString('title_color')
            ->scalar('heading_font')->maxLength('heading_font', 120)->allowEmptyString('heading_font')
            ->scalar('title_font')->maxLength('title_font', 120)->allowEmptyString('title_font')
            ->scalar('title')->maxLength('title', 160)->requirePresence('title', 'create')->notEmptyString('title')
            ->scalar('slogan_color')->maxLength('slogan_color', 20)->notEmptyString('slogan_color')
            ->scalar('slogan_font')->maxLength('slogan_font', 120)->allowEmptyString('slogan_font')
            ->scalar('slogan')->maxLength('slogan', 220)->allowEmptyString('slogan')
            ->scalar('category_layout')->inList('category_layout', ['normal', 'blocks'])->notEmptyString('category_layout')
            ->boolean('show_product_action')
            ->allowEmptyString('intro_text');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['site_id'], 'Sites'), ['errorField' => 'site_id']);
        $rules->add($rules->isUnique(['site_id']), ['errorField' => 'site_id']);

        return $rules;
    }
}
