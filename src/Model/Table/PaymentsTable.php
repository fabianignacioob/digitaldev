<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

class PaymentsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('payments');
        $this->setDisplayField('provider_reference');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->belongsTo('Users');
        $this->belongsTo('Subscriptions');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('user_id')->notEmptyString('user_id')
            ->integer('subscription_id')->allowEmptyString('subscription_id')
            ->scalar('plan_slug')->maxLength('plan_slug', 40)->notEmptyString('plan_slug')
            ->scalar('status')->maxLength('status', 30)->notEmptyString('status')
            ->inList('status', ['pending', 'authorized', 'paid', 'rejected', 'canceled', 'expired', 'refunded', 'reversed', 'failed'])
            ->decimal('amount')->notEmptyString('amount')
            ->decimal('expected_amount')->allowEmptyString('expected_amount')
            ->decimal('confirmed_amount')->allowEmptyString('confirmed_amount')
            ->scalar('currency')->maxLength('currency', 10)->notEmptyString('currency')
            ->scalar('provider')->maxLength('provider', 60)->notEmptyString('provider')
            ->scalar('provider_reference')->maxLength('provider_reference', 160)->allowEmptyString('provider_reference')
            ->scalar('internal_reference')->maxLength('internal_reference', 160)->allowEmptyString('internal_reference')
            ->scalar('buy_order')->maxLength('buy_order', 160)->allowEmptyString('buy_order')
            ->scalar('session_id')->maxLength('session_id', 160)->allowEmptyString('session_id')
            ->scalar('gateway_token')->maxLength('gateway_token', 255)->allowEmptyString('gateway_token')
            ->scalar('gateway_url')->maxLength('gateway_url', 500)->allowEmptyString('gateway_url')
            ->allowEmptyString('request_payload')
            ->allowEmptyString('response_payload')
            ->scalar('authorization_code')->maxLength('authorization_code', 80)->allowEmptyString('authorization_code')
            ->scalar('error_code')->maxLength('error_code', 80)->allowEmptyString('error_code')
            ->dateTime('paid_at')->allowEmptyDateTime('paid_at')
            ->dateTime('period_start')->allowEmptyDateTime('period_start')
            ->dateTime('period_end')->allowEmptyDateTime('period_end')
            ->dateTime('processed_at')->allowEmptyDateTime('processed_at')
            ->dateTime('authorized_at')->allowEmptyDateTime('authorized_at')
            ->dateTime('confirmed_at')->allowEmptyDateTime('confirmed_at')
            ->dateTime('canceled_at')->allowEmptyDateTime('canceled_at')
            ->dateTime('gateway_created_at')->allowEmptyDateTime('gateway_created_at')
            ->dateTime('gateway_expires_at')->allowEmptyDateTime('gateway_expires_at')
            ->dateTime('gateway_commit_started_at')->allowEmptyDateTime('gateway_commit_started_at');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->isUnique(['internal_reference']), ['errorField' => 'internal_reference']);
        $rules->add($rules->isUnique(['buy_order']), ['errorField' => 'buy_order']);
        $rules->add($rules->isUnique(['gateway_token']), ['errorField' => 'gateway_token']);

        return $rules;
    }
}
