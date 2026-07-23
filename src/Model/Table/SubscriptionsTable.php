<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class SubscriptionsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('subscriptions');
        $this->setDisplayField('plan_slug');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->belongsTo('Users');
        $this->hasMany('Payments');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('user_id')->notEmptyString('user_id')
            ->scalar('plan_slug')->maxLength('plan_slug', 40)->notEmptyString('plan_slug')
            ->scalar('status')->maxLength('status', 30)->notEmptyString('status')
            ->inList('status', ['active', 'expiring', 'grace_period', 'expired', 'suspended', 'cancelled'])
            ->dateTime('starts_at')->notEmptyDateTime('starts_at')
            ->dateTime('ends_at')->allowEmptyDateTime('ends_at')
            ->dateTime('grace_ends_at')->allowEmptyDateTime('grace_ends_at')
            ->dateTime('last_processed_at')->allowEmptyDateTime('last_processed_at')
            ->allowEmptyString('notes');
    }
}
