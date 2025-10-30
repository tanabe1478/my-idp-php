<?php
declare(strict_types=1);

namespace App\Service\Social;

/**
 * Social Authentication Provider Interface
 *
 * Defines the contract for OAuth2 provider adapters (Google, GitHub, etc.)
 */
interface SocialAuthProviderInterface
{
    /**
     * Get authorization URL
     *
     * @param string $redirectUri Callback URL
     * @param array $options Additional options (scope, state, etc.)
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(string $redirectUri, array $options = []): string;

    /**
     * Get access token using authorization code
     *
     * @param string $code Authorization code
     * @param string $redirectUri Callback URL
     * @return array Token data [access_token, refresh_token, expires_in]
     */
    public function getAccessToken(string $code, string $redirectUri): array;

    /**
     * Get user profile using access token
     *
     * @param string $accessToken Access token
     * @return array User profile [id, email, name, avatar_url, raw]
     */
    public function getUserProfile(string $accessToken): array;

    /**
     * Get provider name
     *
     * @return string Provider name (google, github)
     */
    public function getProviderName(): string;
}
