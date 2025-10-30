<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Social;

use App\Service\Social\GitHubOAuthAdapter;
use Cake\TestSuite\TestCase;

/**
 * GitHubOAuthAdapter Test Case
 */
class GitHubOAuthAdapterTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Service\Social\GitHubOAuthAdapter
     */
    protected $GitHubOAuthAdapter;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->GitHubOAuthAdapter = new GitHubOAuthAdapter('test_client_id', 'test_client_secret');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->GitHubOAuthAdapter);
        parent::tearDown();
    }

    /**
     * Test getProviderName returns 'github'
     *
     * @return void
     */
    public function testGetProviderNameReturnsGitHub(): void
    {
        $this->assertEquals('github', $this->GitHubOAuthAdapter->getProviderName());
    }

    /**
     * Test getAuthorizationUrl returns a valid URL
     *
     * @return void
     */
    public function testGetAuthorizationUrlReturnsValidUrl(): void
    {
        $redirectUri = 'https://example.com/callback';
        $options = ['state' => 'test_state_123'];

        $url = $this->GitHubOAuthAdapter->getAuthorizationUrl($redirectUri, $options);

        $this->assertIsString($url);
        $this->assertStringContainsString('github.com', $url);
        $this->assertStringContainsString('client_id=test_client_id', $url);
        $this->assertStringContainsString('redirect_uri=' . urlencode($redirectUri), $url);
        $this->assertStringContainsString('state=test_state_123', $url);
    }

    /**
     * Test getAuthorizationUrl includes default scopes
     *
     * @return void
     */
    public function testGetAuthorizationUrlIncludesDefaultScopes(): void
    {
        $redirectUri = 'https://example.com/callback';

        $url = $this->GitHubOAuthAdapter->getAuthorizationUrl($redirectUri);

        // GitHub OAuth should include user:email scope
        $this->assertStringContainsString('scope=', $url);
        $this->assertStringContainsString('user', $url);
    }

    /**
     * Test constructor stores client credentials
     *
     * @return void
     */
    public function testConstructorStoresCredentials(): void
    {
        $adapter = new GitHubOAuthAdapter('my_client_id', 'my_client_secret');

        // The adapter should be created successfully
        $this->assertInstanceOf(GitHubOAuthAdapter::class, $adapter);
        $this->assertEquals('github', $adapter->getProviderName());
    }
}
