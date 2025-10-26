<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'username' => 'testuser1',
                'email' => 'testuser1@example.com',
                // パスワード: "password123"
                'password_hash' => '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5OfmVVWNPV1q6',
                'is_active' => true,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                'username' => 'testuser2',
                'email' => 'testuser2@example.com',
                // パスワード: "password456"
                'password_hash' => '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5OfmVVWNPV1q6',
                'is_active' => true,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'username' => 'inactiveuser',
                'email' => 'inactive@example.com',
                // パスワード: "password789"
                'password_hash' => '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5OfmVVWNPV1q6',
                'is_active' => false,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
