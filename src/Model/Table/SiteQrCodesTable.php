<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class SiteQrCodesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('site_qr_codes');
        $this->setDisplayField('public_token');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Sites');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('site_id')->notEmptyString('site_id')
            ->scalar('public_token')->maxLength('public_token', 64)->notEmptyString('public_token')
            ->add('public_token', 'format', [
                'rule' => ['custom', '/^[a-z0-9]{24,64}$/'],
                'message' => 'El identificador público del código QR no es válido.',
            ])
            ->scalar('frame_style')->inList('frame_style', ['square', 'rounded'])->notEmptyString('frame_style')
            ->dateTime('generated_at')->notEmptyDateTime('generated_at');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['site_id'], 'Sites'), ['errorField' => 'site_id']);
        $rules->add($rules->isUnique(['site_id']), ['errorField' => 'site_id']);
        $rules->add($rules->isUnique(['public_token']), ['errorField' => 'public_token']);

        return $rules;
    }
}
