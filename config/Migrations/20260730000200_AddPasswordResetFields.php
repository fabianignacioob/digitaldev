<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPasswordResetFields extends BaseMigration
{
    /** Adds the fields used by one-time password reset links. */
    public function change(): void
    {
        $this->table('users')
            ->addColumn('password_reset_token_hash', 'string', ['limit' => 64, 'null' => true])
            ->addColumn('password_reset_expires', 'datetime', ['null' => true])
            ->addColumn('password_reset_requested_at', 'datetime', ['null' => true])
            ->addIndex(['password_reset_token_hash'], ['unique' => true])
            ->update();
    }
}
