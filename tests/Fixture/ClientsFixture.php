<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ClientsFixture
 */
class ClientsFixture extends TestFixture
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
                'client_id' => 'test-client-1',
                'client_secret' => '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5ojHGzSW.oYjS', // hashed 'secret'
                'name' => 'Test Client 1',
                'redirect_uris' => '["https://example.com/callback"]',
                'grant_types' => '["authorization_code","refresh_token"]',
                'is_confidential' => true,
                'is_active' => true,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                'client_id' => 'test-client-2',
                'client_secret' => null,
                'name' => 'Test Client 2',
                'redirect_uris' => '["https://example2.com/callback"]',
                'grant_types' => '["implicit"]',
                'is_confidential' => false,
                'is_active' => true,
                'created' => '2025-01-02 00:00:00',
                'modified' => '2025-01-02 00:00:00',
            ],
        ];
        parent::init();
    }
}
