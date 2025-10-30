<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Social;

use App\Service\Social\SocialAuthService;
use Cake\TestSuite\TestCase;

/**
 * SocialAuthService Test Case
 */
class SocialAuthServiceTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Service\Social\SocialAuthService
     */
    protected $SocialAuthService;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.SocialAccounts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            'google' => [
                'clientId' => 'test_google_client_id',
                'clientSecret' => 'test_google_client_secret',
            ],
            'github' => [
                'clientId' => 'test_github_client_id',
                'clientSecret' => 'test_github_client_secret',
            ],
        ];

        $this->SocialAuthService = new SocialAuthService($config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SocialAuthService);
        parent::tearDown();
    }

    /**
     * Test getAuthorizationUrl for Google
     *
     * @return void
     */
    public function testGetAuthorizationUrlForGoogle(): void
    {
        $redirectUri = 'https://example.com/callback';
        $url = $this->SocialAuthService->getAuthorizationUrl('google', $redirectUri, ['state' => 'test123']);

        $this->assertIsString($url);
        $this->assertStringContainsString('accounts.google.com', $url);
        $this->assertStringContainsString('state=test123', $url);
    }

    /**
     * Test getAuthorizationUrl for GitHub
     *
     * @return void
     */
    public function testGetAuthorizationUrlForGitHub(): void
    {
        $redirectUri = 'https://example.com/callback';
        $url = $this->SocialAuthService->getAuthorizationUrl('github', $redirectUri, ['state' => 'test123']);

        $this->assertIsString($url);
        $this->assertStringContainsString('github.com', $url);
        $this->assertStringContainsString('state=test123', $url);
    }

    /**
     * Test getAuthorizationUrl with unsupported provider throws exception
     *
     * @return void
     */
    public function testGetAuthorizationUrlWithUnsupportedProviderThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported provider: invalid');

        $this->SocialAuthService->getAuthorizationUrl('invalid', 'https://example.com/callback');
    }

    /**
     * Test getProvider returns correct adapter for Google
     *
     * @return void
     */
    public function testGetProviderReturnsGoogleAdapter(): void
    {
        $provider = $this->SocialAuthService->getProvider('google');

        $this->assertInstanceOf(\App\Service\Social\GoogleOAuthAdapter::class, $provider);
        $this->assertEquals('google', $provider->getProviderName());
    }

    /**
     * Test getProvider returns correct adapter for GitHub
     *
     * @return void
     */
    public function testGetProviderReturnsGitHubAdapter(): void
    {
        $provider = $this->SocialAuthService->getProvider('github');

        $this->assertInstanceOf(\App\Service\Social\GitHubOAuthAdapter::class, $provider);
        $this->assertEquals('github', $provider->getProviderName());
    }

    /**
     * Test getProvider with unsupported provider throws exception
     *
     * @return void
     */
    public function testGetProviderWithUnsupportedProviderThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported provider: invalid');

        $this->SocialAuthService->getProvider('invalid');
    }
}
