<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AuthorizationCodesFixture
 */
class AuthorizationCodesFixture extends TestFixture
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
                'id' => '77777777-7777-7777-7777-777777777777',
                'code' => 'test_auth_code_1',
                'client_id' => '11111111-1111-1111-1111-111111111111',
                'user_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'redirect_uri' => 'http://localhost:3000/callback',
                'scopes' => '["openid","profile","email"]',
                'expires_at' => '2025-12-31 23:59:59', // Valid
                'is_used' => false,
                'code_challenge' => null,
                'code_challenge_method' => null,
                'created' => '2025-10-27 00:00:00',
                'modified' => '2025-10-27 00:00:00',
            ],
        ];

        parent::init();
    }
}
