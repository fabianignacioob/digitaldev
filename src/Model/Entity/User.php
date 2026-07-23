<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class User extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'email' => true,
        'password' => true,
        'role' => true,
        'active' => true,
        'email_verified' => true,
        'verification_code_hash' => true,
        'verification_expires' => true,
        'verification_sent_at' => true,
        'created' => true,
        'modified' => true,
        'sites' => true,
        'subscriptions' => true,
        'payments' => true,
    ];

    protected array $_hidden = [
        'password',
    ];

    protected function _setPassword(string $password): ?string
    {
        if (strlen($password) === 0) {
            return null;
        }

        return password_hash($password, PASSWORD_DEFAULT);
    }
}
