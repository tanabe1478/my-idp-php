<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\UnauthorizedException;

/**
 * OAuth Controller
 *
 * Handles OAuth2/OpenID Connect authorization flow
 *
 * @property \App\Model\Table\ClientsTable $Clients
 * @property \App\Model\Table\AuthorizationCodesTable $AuthorizationCodes
 * @property \App\Model\Table\RefreshTokensTable $RefreshTokens
 */
class OauthController extends AppController
{
    /**
     * JWT Service instance
     *
     * @var \App\Service\JwtService
     */
    protected $jwtService;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Load required models using CakePHP 5 fetchTable()
        $this->Clients = $this->fetchTable('Clients');
        $this->AuthorizationCodes = $this->fetchTable('AuthorizationCodes');
        $this->RefreshTokens = $this->fetchTable('RefreshTokens');

        // Initialize JWT service (uses default secret for now)
        // TODO: In production, configure this with a persistent secret key
        $this->jwtService = new \App\Service\JwtService();

        // Authorization endpoint is public (handles auth internally)
        // Token endpoint is also public (uses client authentication)
        // UserInfo endpoint requires Bearer token authentication
        $this->Authentication->allowUnauthenticated(['authorize', 'token']);
    }

    /**
     * Authorization endpoint
     *
     * Handles OAuth2 authorization requests
     * GET /oauth/authorize - Show consent screen
     * POST /oauth/authorize - Process user consent
     *
     * @return \Cake\Http\Response|null|void
     */
    public function authorize()
    {
        // Handle POST request (user consent)
        if ($this->request->is('post')) {
            return $this->_handleConsent();
        }

        // Handle GET request (show consent screen)
        return $this->_showConsentScreen();
    }

    /**
     * Show consent screen (GET /oauth/authorize)
     *
     * @return \Cake\Http\Response|null|void
     */
    protected function _showConsentScreen()
    {

        // Get query parameters
        $responseType = $this->request->getQuery('response_type');
        $clientId = $this->request->getQuery('client_id');
        $redirectUri = $this->request->getQuery('redirect_uri');
        $scope = $this->request->getQuery('scope', '');
        $state = $this->request->getQuery('state');

        // Validate required parameters
        if (!$responseType || !$clientId || !$redirectUri) {
            throw new BadRequestException('Missing required parameters: response_type, client_id, redirect_uri');
        }

        // Only support authorization code flow for now
        if ($responseType !== 'code') {
            throw new BadRequestException('Unsupported response_type. Only "code" is supported.');
        }

        // Find and validate client
        $client = $this->Clients->findByClientId($clientId);
        if (!$client) {
            throw new BadRequestException('Invalid client_id');
        }

        if (!$client->is_active) {
            throw new BadRequestException('Client is not active');
        }

        // Validate redirect_uri
        if (!in_array($redirectUri, $client->redirect_uris)) {
            throw new BadRequestException('Invalid redirect_uri');
        }

        // Check if user is authenticated
        $user = $this->Authentication->getIdentity();
        if (!$user) {
            // Store authorization request in session and redirect to login
            $this->request->getSession()->write('OAuth.authorization_request', [
                'response_type' => $responseType,
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => $scope,
                'state' => $state,
            ]);

            return $this->redirect([
                'controller' => 'Users',
                'action' => 'login',
                '?' => ['redirect' => $this->request->getRequestTarget()],
            ]);
        }

        // Parse requested scopes
        $requestedScopes = array_filter(explode(' ', $scope));

        // Show consent screen
        $this->set(compact('client', 'redirectUri', 'requestedScopes', 'state'));
    }

    /**
     * Handle user consent (POST /oauth/authorize)
     *
     * @return \Cake\Http\Response|null
     */
    protected function _handleConsent()
    {

        // Check if user is authenticated
        $user = $this->Authentication->getIdentity();
        if (!$user) {
            throw new UnauthorizedException('User must be authenticated');
        }

        // Get form data
        $clientId = $this->request->getData('client_id');
        $redirectUri = $this->request->getData('redirect_uri');
        $state = $this->request->getData('state');
        $approved = $this->request->getData('approved');
        $scopes = $this->request->getData('scopes', []);

        // Validate required data
        if (!$clientId || !$redirectUri) {
            throw new BadRequestException('Missing required parameters');
        }

        // Find client
        $client = $this->Clients->findByClientId($clientId);
        if (!$client) {
            throw new BadRequestException('Invalid client');
        }

        // Check if user denied authorization
        if ($approved !== '1') {
            // Redirect with error
            $params = [
                'error' => 'access_denied',
                'error_description' => 'User denied authorization',
            ];
            if ($state) {
                $params['state'] = $state;
            }

            return $this->redirect($redirectUri . '?' . http_build_query($params));
        }

        // Generate authorization code
        $code = $this->generateAuthorizationCode();

        // Calculate expiration (10 minutes from now)
        $expiresAt = new \DateTime('+10 minutes');

        // Create authorization code record
        $authCode = $this->AuthorizationCodes->newEntity([
            'code' => $code,
            'client_id' => $client->id,
            'user_id' => $user->getIdentifier(),
            'redirect_uri' => $redirectUri,
            'scopes' => $scopes,
            'expires_at' => $expiresAt,
            'is_used' => false,
        ]);

        if (!$this->AuthorizationCodes->save($authCode)) {
            throw new \RuntimeException('Failed to save authorization code');
        }

        // Redirect back to client with authorization code
        $params = ['code' => $code];
        if ($state) {
            $params['state'] = $state;
        }

        return $this->redirect($redirectUri . '?' . http_build_query($params));
    }

    /**
     * Token endpoint
     *
     * Exchanges authorization code for access token
     * POST /oauth/token
     *
     * @return void
     */
    public function token()
    {
        // Only accept POST requests
        if (!$this->request->is('post')) {
            throw new BadRequestException('Token endpoint only accepts POST requests');
        }

        // Set JSON response
        $this->viewBuilder()->setClassName('Json');

        // Get request parameters
        $grantType = $this->request->getData('grant_type');
        $clientId = $this->request->getData('client_id');
        $clientSecret = $this->request->getData('client_secret');

        // Validate required parameters
        if (!$grantType || !$clientId) {
            $this->set([
                'error' => 'invalid_request',
                'error_description' => 'Missing required parameters',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(400);

            return;
        }

        // Route to appropriate grant type handler
        if ($grantType === 'authorization_code') {
            $this->_handleAuthorizationCodeGrant($clientId, $clientSecret);
        } elseif ($grantType === 'refresh_token') {
            $this->_handleRefreshTokenGrant($clientId, $clientSecret);
        } else {
            $this->set([
                'error' => 'unsupported_grant_type',
                'error_description' => 'Unsupported grant type',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(400);
        }
    }

    /**
     * Handle authorization_code grant type
     *
     * @param string $clientId Client ID
     * @param string|null $clientSecret Client secret
     * @return void
     */
    protected function _handleAuthorizationCodeGrant(string $clientId, ?string $clientSecret): void
    {
        $code = $this->request->getData('code');
        $redirectUri = $this->request->getData('redirect_uri');

        // Validate required parameters for authorization_code grant
        if (!$code || !$redirectUri) {
            $this->set([
                'error' => 'invalid_request',
                'error_description' => 'Missing required parameters',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(400);

            return;
        }

        // Load ClientAuthenticationService
        $clientAuth = new \App\Service\ClientAuthenticationService($this->Clients);

        // Authenticate client
        $client = $clientAuth->authenticate($clientId, $clientSecret);
        if (!$client) {
            $this->set([
                'error' => 'invalid_client',
                'error_description' => 'Client authentication failed',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(401);

            return;
        }

        // Find authorization code
        $authCode = $this->AuthorizationCodes->findByCode($code);
        if (!$authCode) {
            $this->set([
                'error' => 'invalid_grant',
                'error_description' => 'Authorization code not found',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(400);

            return;
        }

        // Validate authorization code
        if ($authCode->client_id !== $client->id) {
            $this->set([
                'error' => 'invalid_grant',
                'error_description' => 'Authorization code was issued to another client',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(400);

            return;
        }

        if ($authCode->redirect_uri !== $redirectUri) {
            $this->set([
                'error' => 'invalid_grant',
                'error_description' => 'Redirect URI mismatch',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(400);

            return;
        }

        if (!$authCode->isValid()) {
            $this->set([
                'error' => 'invalid_grant',
                'error_description' => 'Authorization code is expired or already used',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(400);

            return;
        }

        // Mark authorization code as used
        $authCode->is_used = true;
        $this->AuthorizationCodes->save($authCode);

        // Generate JWT tokens
        $accessToken = $this->jwtService->generateAccessToken(
            $client->client_id,
            $authCode->user_id,
            $authCode->scopes,
            3600 // 1 hour
        );

        $response = [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => implode(' ', $authCode->scopes),
        ];

        // Generate ID token if openid scope was requested
        if (in_array('openid', $authCode->scopes)) {
            $user = $authCode->user;
            $idToken = $this->jwtService->generateIdToken(
                $client->client_id,
                $user->id,
                $user->username,
                $user->email,
                $authCode->scopes,
                3600
            );
            $response['id_token'] = $idToken;
        }

        // Generate and save refresh token
        $refreshTokenString = $this->generateRefreshToken();
        $refreshTokenExpiry = new \DateTime('+30 days'); // Refresh tokens last 30 days

        $refreshToken = $this->RefreshTokens->newEntity([
            'token' => $refreshTokenString,
            'client_id' => $client->id,
            'user_id' => $authCode->user_id,
            'scopes' => $authCode->scopes,
            'expires_at' => $refreshTokenExpiry,
            'is_revoked' => false,
        ]);

        if (!$this->RefreshTokens->save($refreshToken)) {
            throw new \RuntimeException('Failed to save refresh token');
        }

        $response['refresh_token'] = $refreshTokenString;

        $this->set($response);
        $this->viewBuilder()->setOption('serialize', array_keys($response));
    }

    /**
     * Handle refresh_token grant type
     *
     * @param string $clientId Client ID
     * @param string|null $clientSecret Client secret
     * @return void
     */
    protected function _handleRefreshTokenGrant(string $clientId, ?string $clientSecret): void
    {
        $refreshTokenString = $this->request->getData('refresh_token');

        // Validate required parameters for refresh_token grant
        if (!$refreshTokenString) {
            $this->set([
                'error' => 'invalid_request',
                'error_description' => 'Missing refresh_token parameter',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(400);

            return;
        }

        // Load ClientAuthenticationService
        $clientAuth = new \App\Service\ClientAuthenticationService($this->Clients);

        // Authenticate client
        $client = $clientAuth->authenticate($clientId, $clientSecret);
        if (!$client) {
            $this->set([
                'error' => 'invalid_client',
                'error_description' => 'Client authentication failed',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(401);

            return;
        }

        // Find refresh token
        $refreshToken = $this->RefreshTokens->findByToken($refreshTokenString);
        if (!$refreshToken) {
            $this->set([
                'error' => 'invalid_grant',
                'error_description' => 'Refresh token not found',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(400);

            return;
        }

        // Validate refresh token
        if ($refreshToken->client_id !== $client->id) {
            $this->set([
                'error' => 'invalid_grant',
                'error_description' => 'Refresh token was issued to another client',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(400);

            return;
        }

        if (!$refreshToken->isValid()) {
            $this->set([
                'error' => 'invalid_grant',
                'error_description' => 'Refresh token is expired or revoked',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(400);

            return;
        }

        // Generate new access token
        $accessToken = $this->jwtService->generateAccessToken(
            $client->client_id,
            $refreshToken->user_id,
            $refreshToken->scopes,
            3600 // 1 hour
        );

        $response = [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => implode(' ', $refreshToken->scopes),
        ];

        // Token rotation: Generate new refresh token and revoke old one
        $newRefreshTokenString = $this->generateRefreshToken();
        $newRefreshTokenExpiry = new \DateTime('+30 days');

        $newRefreshToken = $this->RefreshTokens->newEntity([
            'token' => $newRefreshTokenString,
            'client_id' => $client->id,
            'user_id' => $refreshToken->user_id,
            'scopes' => $refreshToken->scopes,
            'expires_at' => $newRefreshTokenExpiry,
            'is_revoked' => false,
        ]);

        if (!$this->RefreshTokens->save($newRefreshToken)) {
            throw new \RuntimeException('Failed to save new refresh token');
        }

        // Revoke the old refresh token
        $refreshToken->is_revoked = true;
        $this->RefreshTokens->save($refreshToken);

        $response['refresh_token'] = $newRefreshTokenString;

        $this->set($response);
        $this->viewBuilder()->setOption('serialize', array_keys($response));
    }

    /**
     * Generate a cryptographically secure authorization code
     *
     * @return string
     */
    protected function generateAuthorizationCode(): string
    {
        return bin2hex(random_bytes(32)); // 64 character hex string
    }

    /**
     * Generate a cryptographically secure refresh token
     *
     * @return string
     */
    protected function generateRefreshToken(): string
    {
        return bin2hex(random_bytes(32)); // 64 character hex string
    }

    /**
     * UserInfo endpoint (OpenID Connect)
     *
     * Returns claims about the authenticated user
     * GET /oauth/userinfo
     *
     * @return void
     */
    public function userinfo()
    {
        // Set JSON response
        $this->viewBuilder()->setClassName('Json');

        // Get Bearer token from Authorization header or query parameter
        $authHeader = $this->request->getHeaderLine('Authorization');
        $accessToken = null;

        if (!empty($authHeader) && preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            $accessToken = $matches[1];
        } elseif ($this->request->getQuery('access_token')) {
            // Support access_token in query parameter (for testing convenience)
            $accessToken = $this->request->getQuery('access_token');
        }

        if (empty($accessToken)) {
            $this->set([
                'error' => 'invalid_token',
                'error_description' => 'Missing or invalid Authorization header',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(401);

            return;
        }

        // Verify and decode the access token
        try {
            $payload = $this->jwtService->verifyToken($accessToken);
        } catch (\Exception $e) {
            $this->set([
                'error' => 'invalid_token',
                'error_description' => 'Invalid or expired access token',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(401);

            return;
        }

        // Get user ID from token payload
        $userId = $payload->sub;

        // Load user from database
        $Users = $this->fetchTable('Users');
        $user = $Users->get($userId);

        if (!$user) {
            $this->set([
                'error' => 'invalid_token',
                'error_description' => 'User not found',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error', 'error_description']);
            $this->response = $this->response->withStatus(401);

            return;
        }

        // Parse scopes from token
        $scopes = isset($payload->scope) ? explode(' ', $payload->scope) : [];

        // Build UserInfo response based on scopes
        $userInfo = [
            'sub' => $user->id,
        ];

        // Add profile claims if profile scope was granted
        if (in_array('profile', $scopes)) {
            $userInfo['preferred_username'] = $user->username;
        }

        // Add email claims if email scope was granted
        if (in_array('email', $scopes)) {
            $userInfo['email'] = $user->email;
            $userInfo['email_verified'] = true;
        }

        $this->set($userInfo);
        $this->viewBuilder()->setOption('serialize', array_keys($userInfo));
    }
}
