<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Payment extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'subscription_id' => true,
        'plan_slug' => true,
        'billing_cycle' => true,
        'status' => true,
        'amount' => true,
        'expected_amount' => true,
        'confirmed_amount' => true,
        'currency' => true,
        'provider' => true,
        'provider_reference' => true,
        'internal_reference' => true,
        'buy_order' => true,
        'session_id' => true,
        'gateway_token' => true,
        'gateway_url' => true,
        'gateway_created_at' => true,
        'gateway_expires_at' => true,
        'gateway_commit_started_at' => true,
        'paid_at' => true,
        'period_start' => true,
        'period_end' => true,
        'processed_at' => true,
        'request_payload' => true,
        'response_payload' => true,
        'authorization_code' => true,
        'error_code' => true,
        'authorized_at' => true,
        'confirmed_at' => true,
        'canceled_at' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'subscription' => true,
    ];
}
