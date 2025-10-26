<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RefreshTokensTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RefreshTokensTable Test Case
 */
class RefreshTokensTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RefreshTokensTable
     */
    protected $RefreshTokens;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.RefreshTokens',
        'app.Clients',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('RefreshTokens') ? [] : ['className' => RefreshTokensTable::class];
        $this->RefreshTokens = $this->getTableLocator()->get('RefreshTokens', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->RefreshTokens);
        parent::tearDown();
    }

    /**
     * Test validationDefault
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $refreshToken = $this->RefreshTokens->newEmptyEntity();
        $refreshToken = $this->RefreshTokens->patchEntity($refreshToken, []);

        $errors = $refreshToken->getErrors();

        $this->assertArrayHasKey('token', $errors);
        $this->assertArrayHasKey('client_id', $errors);
        $this->assertArrayHasKey('user_id', $errors);
        $this->assertArrayHasKey('scopes', $errors);
        $this->assertArrayHasKey('expires_at', $errors);
    }

    /**
     * Test findByToken finds refresh token
     *
     * @return void
     */
    public function testFindByTokenFindsRefreshToken(): void
    {
        $result = $this->RefreshTokens->findByToken('test_refresh_token_1');

        $this->assertNotNull($result);
        $this->assertEquals('test_refresh_token_1', $result->token);
    }

    /**
     * Test findByToken returns null for non-existent token
     *
     * @return void
     */
    public function testFindByTokenReturnsNullForNonExistentToken(): void
    {
        $result = $this->RefreshTokens->findByToken('non_existent_token');

        $this->assertNull($result);
    }

    /**
     * Test cleanupExpired deletes expired tokens
     *
     * @return void
     */
    public function testCleanupExpiredDeletesExpiredTokens(): void
    {
        // Verify expired token exists before cleanup
        $expiredToken = $this->RefreshTokens->get('22222222-2222-2222-2222-222222222222');
        $this->assertNotNull($expiredToken);

        // Run cleanup
        $deletedCount = $this->RefreshTokens->cleanupExpired();

        // Verify expired token was deleted
        $this->assertEquals(1, $deletedCount);

        // Verify token no longer exists
        $this->assertFalse($this->RefreshTokens->exists(['id' => '22222222-2222-2222-2222-222222222222']));
    }

    /**
     * Test cleanupExpired does not delete valid tokens
     *
     * @return void
     */
    public function testCleanupExpiredDoesNotDeleteValidTokens(): void
    {
        // Verify valid token exists before cleanup
        $validToken = $this->RefreshTokens->get('11111111-1111-1111-1111-111111111111');
        $this->assertNotNull($validToken);

        // Run cleanup
        $this->RefreshTokens->cleanupExpired();

        // Verify valid token still exists
        $this->assertTrue($this->RefreshTokens->exists(['id' => '11111111-1111-1111-1111-111111111111']));
    }

    /**
     * Test revokeByToken marks token as revoked
     *
     * @return void
     */
    public function testRevokeByTokenMarksTokenAsRevoked(): void
    {
        $token = 'test_refresh_token_1';

        // Verify token is not revoked initially
        $refreshToken = $this->RefreshTokens->findByToken($token);
        $this->assertFalse($refreshToken->is_revoked);

        // Revoke the token
        $result = $this->RefreshTokens->revokeByToken($token);

        $this->assertTrue($result);

        // Verify token is now revoked
        $refreshToken = $this->RefreshTokens->findByToken($token);
        $this->assertTrue($refreshToken->is_revoked);
    }

    /**
     * Test revokeByToken returns false for non-existent token
     *
     * @return void
     */
    public function testRevokeByTokenReturnsFalseForNonExistentToken(): void
    {
        $result = $this->RefreshTokens->revokeByToken('non_existent_token');

        $this->assertFalse($result);
    }
}
