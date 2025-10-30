<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\SocialAccount;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\SocialAccount Test Case
 */
class SocialAccountTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Entity\SocialAccount
     */
    protected $SocialAccount;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->SocialAccount = new SocialAccount();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SocialAccount);
        parent::tearDown();
    }

    /**
     * Test accessible fields
     *
     * @return void
     */
    public function testAccessibleFields(): void
    {
        $data = [
            'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            'provider' => 'google',
            'provider_user_id' => 'google123',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_123',
            'expires_at' => new \DateTime('+1 hour'),
            'raw_data' => ['key' => 'value'],
        ];

        $socialAccount = new SocialAccount($data);

        $this->assertEquals($data['user_id'], $socialAccount->user_id);
        $this->assertEquals($data['provider'], $socialAccount->provider);
        $this->assertEquals($data['provider_user_id'], $socialAccount->provider_user_id);
        $this->assertEquals($data['email'], $socialAccount->email);
        $this->assertEquals($data['name'], $socialAccount->name);
        $this->assertEquals($data['avatar_url'], $socialAccount->avatar_url);
    }

    /**
     * Test hidden fields
     *
     * @return void
     */
    public function testHiddenFields(): void
    {
        $socialAccount = new SocialAccount([
            'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            'provider' => 'google',
            'provider_user_id' => 'google123',
            'access_token' => 'secret_access_token',
            'refresh_token' => 'secret_refresh_token',
        ]);

        $array = $socialAccount->toArray();

        $this->assertArrayNotHasKey('access_token_encrypted', $array);
        $this->assertArrayNotHasKey('refresh_token_encrypted', $array);
    }

    /**
     * Test access token encryption setter
     *
     * @return void
     */
    public function testAccessTokenEncryption(): void
    {
        $socialAccount = new SocialAccount();
        $plainToken = 'my_access_token_123';

        $socialAccount->access_token = $plainToken;

        // The encrypted value should not be the same as the plain token
        $this->assertNotNull($socialAccount->access_token_encrypted);
        $this->assertNotEquals($plainToken, $socialAccount->access_token_encrypted);
    }

    /**
     * Test access token decryption getter
     *
     * @return void
     */
    public function testAccessTokenDecryption(): void
    {
        $socialAccount = new SocialAccount();
        $plainToken = 'my_access_token_123';

        $socialAccount->access_token = $plainToken;

        // Getting access_token should decrypt and return the original value
        $this->assertEquals($plainToken, $socialAccount->access_token);
    }

    /**
     * Test refresh token encryption setter
     *
     * @return void
     */
    public function testRefreshTokenEncryption(): void
    {
        $socialAccount = new SocialAccount();
        $plainToken = 'my_refresh_token_123';

        $socialAccount->refresh_token = $plainToken;

        // The encrypted value should not be the same as the plain token
        $this->assertNotNull($socialAccount->refresh_token_encrypted);
        $this->assertNotEquals($plainToken, $socialAccount->refresh_token_encrypted);
    }

    /**
     * Test refresh token decryption getter
     *
     * @return void
     */
    public function testRefreshTokenDecryption(): void
    {
        $socialAccount = new SocialAccount();
        $plainToken = 'my_refresh_token_123';

        $socialAccount->refresh_token = $plainToken;

        // Getting refresh_token should decrypt and return the original value
        $this->assertEquals($plainToken, $socialAccount->refresh_token);
    }

    /**
     * Test isTokenExpired() when token is not expired
     *
     * @return void
     */
    public function testIsTokenExpiredReturnsFalseWhenNotExpired(): void
    {
        $socialAccount = new SocialAccount([
            'expires_at' => new \DateTime('+1 hour'),
        ]);

        $this->assertFalse($socialAccount->isTokenExpired());
    }

    /**
     * Test isTokenExpired() when token is expired
     *
     * @return void
     */
    public function testIsTokenExpiredReturnsTrueWhenExpired(): void
    {
        $socialAccount = new SocialAccount([
            'expires_at' => new \DateTime('-1 hour'),
        ]);

        $this->assertTrue($socialAccount->isTokenExpired());
    }

    /**
     * Test isTokenExpired() when expires_at is null
     *
     * @return void
     */
    public function testIsTokenExpiredReturnsFalseWhenExpiresAtIsNull(): void
    {
        $socialAccount = new SocialAccount([
            'expires_at' => null,
        ]);

        $this->assertFalse($socialAccount->isTokenExpired());
    }
}
