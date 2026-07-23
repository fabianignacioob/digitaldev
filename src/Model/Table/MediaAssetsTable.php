<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class MediaAssetsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('media_assets');
        $this->setDisplayField('original_name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Users');
        $this->belongsTo('Sites');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('user_id')->notEmptyString('user_id')
            ->integer('site_id')->allowEmptyString('site_id')
            ->scalar('type')->maxLength('type', 40)->notEmptyString('type')
            ->scalar('path')->maxLength('path', 255)->notEmptyString('path')
            ->scalar('original_name')->maxLength('original_name', 255)->notEmptyString('original_name');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['site_id'], 'Sites'), ['errorField' => 'site_id']);

        return $rules;
    }
}
