<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\RefreshToken;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\RefreshToken Test Case
 */
class RefreshTokenTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Entity\RefreshToken
     */
    protected $RefreshToken;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->RefreshToken = new RefreshToken();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->RefreshToken);
        parent::tearDown();
    }

    /**
     * Test scopes getter converts JSON string to array
     *
     * @return void
     */
    public function testScopesGetterConvertsJsonToArray(): void
    {
        $this->RefreshToken->scopes = '["openid","profile","email"]';

        $result = $this->RefreshToken->scopes;

        $this->assertIsArray($result);
        $this->assertEquals(['openid', 'profile', 'email'], $result);
    }

    /**
     * Test scopes getter handles array input
     *
     * @return void
     */
    public function testScopesGetterHandlesArray(): void
    {
        $scopes = ['openid', 'profile'];
        $this->RefreshToken->scopes = $scopes;

        $result = $this->RefreshToken->scopes;

        $this->assertIsArray($result);
        $this->assertEquals($scopes, $result);
    }

    /**
     * Test scopes getter handles null
     *
     * @return void
     */
    public function testScopesGetterHandlesNull(): void
    {
        $this->RefreshToken->scopes = null;

        $result = $this->RefreshToken->scopes;

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test isExpired returns true for expired tokens
     *
     * @return void
     */
    public function testIsExpiredReturnsTrueForExpiredTokens(): void
    {
        $this->RefreshToken->expires_at = new \DateTime('-1 hour');

        $this->assertTrue($this->RefreshToken->isExpired());
    }

    /**
     * Test isExpired returns false for valid tokens
     *
     * @return void
     */
    public function testIsExpiredReturnsFalseForValidTokens(): void
    {
        $this->RefreshToken->expires_at = new \DateTime('+1 hour');

        $this->assertFalse($this->RefreshToken->isExpired());
    }

    /**
     * Test isValid returns true for valid tokens
     *
     * @return void
     */
    public function testIsValidReturnsTrueForValidTokens(): void
    {
        $this->RefreshToken->expires_at = new \DateTime('+1 hour');
        $this->RefreshToken->is_revoked = false;

        $this->assertTrue($this->RefreshToken->isValid());
    }

    /**
     * Test isValid returns false for expired tokens
     *
     * @return void
     */
    public function testIsValidReturnsFalseForExpiredTokens(): void
    {
        $this->RefreshToken->expires_at = new \DateTime('-1 hour');
        $this->RefreshToken->is_revoked = false;

        $this->assertFalse($this->RefreshToken->isValid());
    }

    /**
     * Test isValid returns false for revoked tokens
     *
     * @return void
     */
    public function testIsValidReturnsFalseForRevokedTokens(): void
    {
        $this->RefreshToken->expires_at = new \DateTime('+1 hour');
        $this->RefreshToken->is_revoked = true;

        $this->assertFalse($this->RefreshToken->isValid());
    }
}
