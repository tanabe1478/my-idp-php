<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\OauthController Test Case
 *
 * Tests the OAuth2 authorization code flow
 */
class OauthControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Clients',
        'app.Scopes',
        'app.ClientsScopes',
        'app.AuthorizationCodes',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // OAuth2 uses 'state' parameter for CSRF protection, not CakePHP CSRF tokens
        // So we don't enable CSRF protection for these tests

        // Setup authenticated session for tests
        $this->session([
            'Auth' => [
                'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'username' => 'testuser1',
                'email' => 'testuser1@example.com',
                'is_active' => true,
            ],
        ]);
    }

    /**
     * Test authorization endpoint shows consent screen
     *
     * @return void
     */
    public function testAuthorizeShowsConsentScreen(): void
    {
        $authUrl = '/oauth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => 'test_client_1',
            'redirect_uri' => 'http://localhost:3000/callback',
            'scope' => 'openid profile email',
            'state' => 'random_state_xyz',
        ]);

        $this->get($authUrl);

        $this->assertResponseOk();
        $this->assertResponseContains('Authorization Request');
        $this->assertResponseContains('Test Client 1');
        $this->assertResponseContains('openid');
        $this->assertResponseContains('profile');
        $this->assertResponseContains('email');
    }

    /**
     * Test authorization endpoint is accessible (handles auth internally)
     *
     * @return void
     */
    public function testAuthorizeIsAccessible(): void
    {
        $authUrl = '/oauth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => 'test_client_1',
            'redirect_uri' => 'http://localhost:3000/callback',
            'scope' => 'openid profile email',
            'state' => 'random_state_xyz',
        ]);

        $this->get($authUrl);

        // Authorization endpoint should be accessible (marked as allowUnauthenticated)
        // It handles authentication internally and redirects if needed
        $this->assertResponseOk();
    }

    /**
     * Test authorization endpoint with invalid client_id
     *
     * @return void
     */
    public function testAuthorizeWithInvalidClientId(): void
    {
        $authUrl = '/oauth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => 'invalid_client_xyz',
            'redirect_uri' => 'http://localhost:3000/callback',
            'scope' => 'openid profile email',
            'state' => 'random_state_xyz',
        ]);

        $this->get($authUrl);
        $this->assertResponseCode(400);
    }

    /**
     * Test token endpoint requires POST
     *
     * @return void
     */
    public function testTokenEndpointRequiresPost(): void
    {
        $this->get('/oauth/token');
        $this->assertResponseCode(400);
    }

    /**
     * Test token endpoint with missing parameters
     *
     * @return void
     */
    public function testTokenEndpointWithMissingParameters(): void
    {
        $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
        ]);

        $this->assertResponseCode(400);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals('invalid_request', $response['error']);
    }

    /**
     * Test token endpoint with invalid grant type
     *
     * @return void
     */
    public function testTokenEndpointWithInvalidGrantType(): void
    {
        $this->post('/oauth/token', [
            'grant_type' => 'unsupported_grant',
            'code' => 'dummy_code',
            'redirect_uri' => 'http://localhost:3000/callback',
            'client_id' => 'test_client_1',
            'client_secret' => 'secret',
        ]);

        $this->assertResponseCode(400);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals('unsupported_grant_type', $response['error']);
    }
}
