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
        'app.RefreshTokens',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Set a consistent Security.salt for JWT token signing/verification in tests
        \Cake\Core\Configure::write('Security.salt', 'test_security_salt_for_jwt_tokens_do_not_use_in_production');

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

    /**
     * Test successful token exchange includes refresh token
     *
     * @return void
     */
    public function testSuccessfulTokenExchangeIncludesRefreshToken(): void
    {
        $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'code' => 'test_auth_code_1',
            'redirect_uri' => 'http://localhost:3000/callback',
            'client_id' => 'test_client_1',
            'client_secret' => 'secret',
        ]);

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        // Assert access token fields
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('token_type', $response);
        $this->assertArrayHasKey('expires_in', $response);
        $this->assertEquals('Bearer', $response['token_type']);
        $this->assertEquals(3600, $response['expires_in']);

        // Assert refresh token is included
        $this->assertArrayHasKey('refresh_token', $response);
        $this->assertNotEmpty($response['refresh_token']);

        // Assert ID token is included (openid scope)
        $this->assertArrayHasKey('id_token', $response);
    }

    /**
     * Test refresh_token grant type exchanges refresh token for new access token
     *
     * @return void
     */
    public function testRefreshTokenGrantTypeExchangesForNewAccessToken(): void
    {
        $this->post('/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => 'test_refresh_token_1',
            'client_id' => 'test_client_1',
            'client_secret' => 'secret',
        ]);

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        // Assert new access token is issued
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('token_type', $response);
        $this->assertArrayHasKey('expires_in', $response);
        $this->assertEquals('Bearer', $response['token_type']);
        $this->assertEquals(3600, $response['expires_in']);

        // Assert new refresh token is issued (token rotation)
        $this->assertArrayHasKey('refresh_token', $response);
        $this->assertNotEmpty($response['refresh_token']);

        // New refresh token should be different from the old one
        $this->assertNotEquals('test_refresh_token_1', $response['refresh_token']);

        // Assert scopes are maintained
        $this->assertArrayHasKey('scope', $response);
        $this->assertEquals('openid profile email', $response['scope']);
    }

    /**
     * Test refresh_token grant type with invalid refresh token
     *
     * @return void
     */
    public function testRefreshTokenGrantTypeWithInvalidToken(): void
    {
        $this->post('/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => 'invalid_refresh_token',
            'client_id' => 'test_client_1',
            'client_secret' => 'secret',
        ]);

        $this->assertResponseCode(400);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals('invalid_grant', $response['error']);
    }

    /**
     * Test refresh_token grant type with expired refresh token
     *
     * @return void
     */
    public function testRefreshTokenGrantTypeWithExpiredToken(): void
    {
        $this->post('/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => 'test_refresh_token_2_expired',
            'client_id' => 'test_client_1',
            'client_secret' => 'secret',
        ]);

        $this->assertResponseCode(400);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals('invalid_grant', $response['error']);
    }

    /**
     * Test refresh_token grant type with revoked refresh token
     *
     * @return void
     */
    public function testRefreshTokenGrantTypeWithRevokedToken(): void
    {
        $this->post('/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => 'test_refresh_token_3_revoked',
            'client_id' => 'test_client_1',
            'client_secret' => 'secret',
        ]);

        $this->assertResponseCode(400);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals('invalid_grant', $response['error']);
    }

    /**
     * Test UserInfo endpoint returns user information
     *
     * @return void
     */
    public function testUserInfoEndpointReturnsUserInformation(): void
    {
        // First, get an access token
        $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'code' => 'test_auth_code_1',
            'redirect_uri' => 'http://localhost:3000/callback',
            'client_id' => 'test_client_1',
            'client_secret' => 'secret',
        ]);

        $this->assertResponseOk();
        $tokenResponse = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('access_token', $tokenResponse);

        $accessToken = $tokenResponse['access_token'];

        // Now, call UserInfo endpoint with the access token
        $this->get('/oauth/userinfo?access_token=' . urlencode($accessToken));
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $userInfo = json_decode((string)$this->_response->getBody(), true);

        // Assert basic claims
        $this->assertArrayHasKey('sub', $userInfo);
        $this->assertEquals('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', $userInfo['sub']);

        // Assert profile scope claims
        $this->assertArrayHasKey('preferred_username', $userInfo);
        $this->assertEquals('testuser1', $userInfo['preferred_username']);

        // Assert email scope claims
        $this->assertArrayHasKey('email', $userInfo);
        $this->assertEquals('testuser1@example.com', $userInfo['email']);
        $this->assertArrayHasKey('email_verified', $userInfo);
    }

    /**
     * Test UserInfo endpoint requires authentication
     *
     * @return void
     */
    public function testUserInfoEndpointRequiresAuthentication(): void
    {
        $this->get('/oauth/userinfo');

        $this->assertResponseCode(401);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals('invalid_token', $response['error']);
    }

    /**
     * Test UserInfo endpoint rejects invalid token
     *
     * @return void
     */
    public function testUserInfoEndpointRejectsInvalidToken(): void
    {
        $this->get('/oauth/userinfo?access_token=invalid_token_xyz');

        $this->assertResponseCode(401);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals('invalid_token', $response['error']);
    }

    /**
     * Test OpenID Connect Discovery document endpoint
     *
     * @return void
     */
    public function testDiscoveryDocumentReturnsMetadata(): void
    {
        $this->get('/.well-known/openid-configuration');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $discovery = json_decode((string)$this->_response->getBody(), true);

        // Required fields per OpenID Connect Discovery spec
        $this->assertArrayHasKey('issuer', $discovery);
        $this->assertArrayHasKey('authorization_endpoint', $discovery);
        $this->assertArrayHasKey('token_endpoint', $discovery);
        $this->assertArrayHasKey('userinfo_endpoint', $discovery);
        $this->assertArrayHasKey('jwks_uri', $discovery);
        $this->assertArrayHasKey('response_types_supported', $discovery);
        $this->assertArrayHasKey('subject_types_supported', $discovery);
        $this->assertArrayHasKey('id_token_signing_alg_values_supported', $discovery);

        // Verify specific values
        $this->assertEquals('http://localhost:8765', $discovery['issuer']);
        $this->assertEquals('http://localhost:8765/oauth/authorize', $discovery['authorization_endpoint']);
        $this->assertEquals('http://localhost:8765/oauth/token', $discovery['token_endpoint']);
        $this->assertEquals('http://localhost:8765/oauth/userinfo', $discovery['userinfo_endpoint']);
        $this->assertEquals('http://localhost:8765/.well-known/jwks.json', $discovery['jwks_uri']);

        // Verify supported features
        $this->assertContains('code', $discovery['response_types_supported']);
        $this->assertContains('public', $discovery['subject_types_supported']);
        $this->assertContains('HS256', $discovery['id_token_signing_alg_values_supported']);
    }

    /**
     * Test JWKS (JSON Web Key Set) endpoint
     *
     * Note: This returns an empty key set for HS256 (symmetric key algorithm)
     * since we cannot expose the secret key. This endpoint exists for spec compliance.
     *
     * @return void
     */
    public function testJwksEndpointReturnsKeySet(): void
    {
        $this->get('/.well-known/jwks.json');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $jwks = json_decode((string)$this->_response->getBody(), true);

        // JWKS structure per RFC 7517
        $this->assertArrayHasKey('keys', $jwks);
        $this->assertIsArray($jwks['keys']);

        // For HS256 (symmetric key), we return empty keys array
        // since we cannot expose the secret key
        $this->assertEmpty($jwks['keys'], 'HS256 uses symmetric keys which should not be exposed');
    }
}
