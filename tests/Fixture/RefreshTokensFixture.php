<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RefreshTokensFixture
 */
class RefreshTokensFixture extends TestFixture
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
                'token' => 'test_refresh_token_1',
                'client_id' => '11111111-1111-1111-1111-111111111111',
                'user_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'scopes' => '["openid","profile","email"]',
                'expires_at' => '2025-12-31 23:59:59',
                'is_revoked' => false,
                'created' => '2025-10-27 00:00:00',
                'modified' => '2025-10-27 00:00:00',
            ],
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'token' => 'test_refresh_token_2_expired',
                'client_id' => '11111111-1111-1111-1111-111111111111',
                'user_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'scopes' => '["openid","profile"]',
                'expires_at' => '2020-01-01 00:00:00', // Expired
                'is_revoked' => false,
                'created' => '2020-01-01 00:00:00',
                'modified' => '2020-01-01 00:00:00',
            ],
            [
                'id' => '33333333-3333-3333-3333-333333333333',
                'token' => 'test_refresh_token_3_revoked',
                'client_id' => '11111111-1111-1111-1111-111111111111',
                'user_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'scopes' => '["openid"]',
                'expires_at' => '2025-12-31 23:59:59',
                'is_revoked' => true, // Revoked
                'created' => '2025-10-27 00:00:00',
                'modified' => '2025-10-27 00:00:00',
            ],
        ];
        parent::init();
    }
}
