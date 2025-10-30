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
                'id' => '11111111-1111-1111-1111-111111111111',
                'client_id' => 'test_client_1',
                'client_secret' => '$2y$12$YxkSqRw47o6UNhqX1fiFt.e.vi36reQa1dSep5MkgrLWxHgmeSO8u', // hashed 'secret'
                'name' => 'Test Client 1',
                'redirect_uris' => '["https://example.com/callback","http://localhost:3000/callback"]',
                'grant_types' => '["authorization_code","refresh_token","client_credentials"]',
                'is_confidential' => true,
                'is_active' => true,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'client_id' => 'test_client_2',
                'client_secret' => null,
                'name' => 'Test Client 2',
                'redirect_uris' => '["https://example2.com/callback"]',
                'grant_types' => '["implicit"]',
                'is_confidential' => false,
                'is_active' => true,
                'created' => '2025-01-02 00:00:00',
                'modified' => '2025-01-02 00:00:00',
            ],
            [
                'id' => '33333333-3333-3333-3333-333333333333',
                'client_id' => 'test_client_3',
                'client_secret' => '$2y$12$YxkSqRw47o6UNhqX1fiFt.e.vi36reQa1dSep5MkgrLWxHgmeSO8u', // hashed 'secret'
                'name' => 'Test Client 3',
                'redirect_uris' => '["https://example3.com/callback"]',
                'grant_types' => '["authorization_code"]',
                'is_confidential' => true,
                'is_active' => false,
                'created' => '2025-01-03 00:00:00',
                'modified' => '2025-01-03 00:00:00',
            ],
        ];
        parent::init();
    }
}
