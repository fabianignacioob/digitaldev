<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPaymentGatewayFields extends BaseMigration
{
    public function change(): void
    {
        $this->table('payments')
            ->changeColumn('paid_at', 'datetime', ['null' => true])
            ->changeColumn('period_start', 'datetime', ['null' => true])
            ->changeColumn('period_end', 'datetime', ['null' => true])
            ->update();

        $this->table('payments')
            ->addColumn('internal_reference', 'string', ['limit' => 160, 'null' => true, 'after' => 'provider_reference'])
            ->addColumn('buy_order', 'string', ['limit' => 160, 'null' => true, 'after' => 'internal_reference'])
            ->addColumn('session_id', 'string', ['limit' => 160, 'null' => true, 'after' => 'buy_order'])
            ->addColumn('expected_amount', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => true, 'after' => 'amount'])
            ->addColumn('confirmed_amount', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => true, 'after' => 'expected_amount'])
            ->addColumn('request_payload', 'json', ['default' => '{}', 'after' => 'processed_at'])
            ->addColumn('response_payload', 'json', ['default' => '{}', 'after' => 'request_payload'])
            ->addColumn('authorization_code', 'string', ['limit' => 80, 'null' => true, 'after' => 'response_payload'])
            ->addColumn('error_code', 'string', ['limit' => 80, 'null' => true, 'after' => 'authorization_code'])
            ->addColumn('authorized_at', 'datetime', ['null' => true, 'after' => 'error_code'])
            ->addColumn('confirmed_at', 'datetime', ['null' => true, 'after' => 'authorized_at'])
            ->addColumn('canceled_at', 'datetime', ['null' => true, 'after' => 'confirmed_at'])
            ->addIndex(['internal_reference'], [
                'unique' => true,
                'name' => 'payments_internal_reference_unique',
            ])
            ->addIndex(['buy_order'], [
                'unique' => true,
                'name' => 'payments_buy_order_unique',
            ])
            ->update();

        $now = date('Y-m-d H:i:s');
        $this->execute(
            "UPDATE payments
             SET internal_reference = COALESCE(internal_reference, provider_reference, 'legacy-' || id),
                 buy_order = COALESCE(buy_order, 'legacy-' || id),
                 session_id = COALESCE(session_id, 'legacy-' || id),
                 expected_amount = COALESCE(expected_amount, amount),
                 confirmed_amount = COALESCE(confirmed_amount, amount),
                 confirmed_at = COALESCE(confirmed_at, paid_at, '{$now}'),
                 authorized_at = COALESCE(authorized_at, paid_at),
                 request_payload = COALESCE(request_payload, '{}'),
                 response_payload = COALESCE(response_payload, '{}')
             WHERE internal_reference IS NULL
                OR buy_order IS NULL
                OR session_id IS NULL
                OR expected_amount IS NULL
                OR confirmed_amount IS NULL"
        );
    }
}
