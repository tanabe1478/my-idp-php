<?php
declare(strict_types=1);

namespace App\Service\Social;

use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\GithubResourceOwner;

/**
 * GitHub OAuth Adapter
 *
 * Implements social authentication for GitHub using League OAuth2 Client
 */
class GitHubOAuthAdapter implements SocialAuthProviderInterface
{
    /**
     * GitHub OAuth2 Provider
     *
     * @var \League\OAuth2\Client\Provider\Github
     */
    private Github $provider;

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
     * @param string $clientId GitHub Client ID
     * @param string $clientSecret GitHub Client Secret
     */
    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        $this->provider = new Github([
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
        $provider = new Github([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $redirectUri,
        ]);

        // Merge default scopes with provided options
        $defaultOptions = [
            'scope' => ['user:email'],
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
        // Create a new provider with redirect URI
        $provider = new Github([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $redirectUri,
        ]);

        $token = $provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

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

        /** @var \League\OAuth2\Client\Provider\GithubResourceOwner $user */
        $user = $this->provider->getResourceOwner($token);

        return [
            'id' => (string)$user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName() ?? $user->getNickname(),
            'avatar_url' => $user->getAvatarUrl(),
            'raw' => $user->toArray(),
        ];
    }

    /**
     * Get provider name
     *
     * @return string Provider name (github)
     */
    public function getProviderName(): string
    {
        return 'github';
    }
}
