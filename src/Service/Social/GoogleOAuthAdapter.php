<?php
declare(strict_types=1);

namespace App\Service\Social;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;

/**
 * Google OAuth Adapter
 *
 * Implements social authentication for Google using League OAuth2 Client
 */
class GoogleOAuthAdapter implements SocialAuthProviderInterface
{
    /**
     * Google OAuth2 Provider
     *
     * @var \League\OAuth2\Client\Provider\Google
     */
    private Google $provider;

    /**
     * Client ID
     *
     * @var string
     */
    private string $clientId;

    /**
     * Client Secret
     *
     * @var string
     */
    private string $clientSecret;

    /**
     * Constructor
     *
     * @param string $clientId Google Client ID
     * @param string $clientSecret Google Client Secret
     */
    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        $this->provider = new Google([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]);
    }

    /**
     * Get authorization URL
     *
     * @param string $redirectUri Callback URL
     * @param array $options Additional options (scope, state, etc.)
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(string $redirectUri, array $options = []): string
    {
        // Create a new provider with redirect URI
        $provider = new Google([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $redirectUri,
        ]);

        // Merge default scopes with provided options
        $defaultOptions = [
            'scope' => ['openid', 'email', 'profile'],
        ];

        $mergedOptions = array_merge($defaultOptions, $options);

        return $provider->getAuthorizationUrl($mergedOptions);
    }

    /**
     * Get access token using authorization code
     *
     * @param string $code Authorization code
     * @param string $redirectUri Callback URL
     * @return array Token data [access_token, refresh_token, expires_in]
     */
    public function getAccessToken(string $code, string $redirectUri): array
    {
        // Debug log
        \Cake\Log\Log::debug("GoogleOAuthAdapter->getAccessToken() called with redirectUri: {$redirectUri}");

        // Create a new provider with redirect URI
        $provider = new Google([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $redirectUri,
        ]);

        \Cake\Log\Log::debug("About to request access token with code: " . substr($code, 0, 10) . "...");

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
        } catch (\Throwable $e) {
            \Cake\Log\Log::error("Failed to get access token: " . $e->getMessage());
            \Cake\Log\Log::error("Provider config - clientId: " . $this->clientId . ", redirectUri: " . $redirectUri);
            \Cake\Log\Log::error("Exception type: " . get_class($e));
            throw $e;
        }

        return [
            'access_token' => $token->getToken(),
            'refresh_token' => $token->getRefreshToken(),
            'expires_in' => $token->getExpires(),
        ];
    }

    /**
     * Get user profile using access token
     *
     * @param string $accessToken Access token
     * @return array User profile [id, email, name, avatar_url, raw]
     */
    public function getUserProfile(string $accessToken): array
    {
        // Create AccessToken object from the access token string
        $token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => $accessToken,
        ]);

        /** @var \League\OAuth2\Client\Provider\GoogleUser $user */
        $user = $this->provider->getResourceOwner($token);

        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'avatar_url' => $user->getAvatar(),
            'raw' => $user->toArray(),
        ];
    }

    /**
     * Get provider name
     *
     * @return string Provider name (google)
     */
    public function getProviderName(): string
    {
        return 'google';
    }
}
