<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SocialAccountsFixture
 */
class SocialAccountsFixture extends TestFixture
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
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'user_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'provider' => 'google',
                'provider_user_id' => 'google_test_user_1',
                'email' => 'google@example.com',
                'name' => 'Google Test User',
                'avatar_url' => 'https://example.com/google-avatar.jpg',
                'access_token_encrypted' => 'encrypted_access_token_1',
                'refresh_token_encrypted' => 'encrypted_refresh_token_1',
                'expires_at' => '2025-12-31 23:59:59',
                'raw_data' => json_encode(['id' => 'google_test_user_1', 'verified_email' => true]),
                'created' => '2025-10-30 00:00:00',
                'modified' => '2025-10-30 00:00:00',
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440002',
                'user_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'provider' => 'github',
                'provider_user_id' => 'github_test_user_1',
                'email' => 'github@example.com',
                'name' => 'GitHub Test User',
                'avatar_url' => 'https://example.com/github-avatar.jpg',
                'access_token_encrypted' => 'encrypted_access_token_2',
                'refresh_token_encrypted' => null,
                'expires_at' => null,
                'raw_data' => json_encode(['id' => 12345, 'login' => 'github_test_user_1']),
                'created' => '2025-10-30 00:00:00',
                'modified' => '2025-10-30 00:00:00',
            ],
        ];
        parent::init();
    }
}
