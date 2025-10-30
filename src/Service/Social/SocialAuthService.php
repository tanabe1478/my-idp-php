<?php
declare(strict_types=1);

namespace App\Service\Social;

use App\Model\Entity\User;
use Cake\ORM\Locator\LocatorAwareTrait;
use InvalidArgumentException;

/**
 * Social Authentication Service
 *
 * Manages social authentication flow across different providers
 */
class SocialAuthService
{
    use LocatorAwareTrait;

    /**
     * Provider adapters
     *
     * @var array<string, \App\Service\Social\SocialAuthProviderInterface>
     */
    private array $providers = [];

    /**
     * Configuration
     *
     * @var array
     */
    private array $config;

    /**
     * Constructor
     *
     * @param array $config Provider configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->initializeProviders();
    }

    /**
     * Initialize provider adapters
     *
     * @return void
     */
    private function initializeProviders(): void
    {
        if (isset($this->config['google'])) {
            $this->providers['google'] = new GoogleOAuthAdapter(
                $this->config['google']['clientId'],
                $this->config['google']['clientSecret']
            );
        }

        if (isset($this->config['github'])) {
            $this->providers['github'] = new GitHubOAuthAdapter(
                $this->config['github']['clientId'],
                $this->config['github']['clientSecret']
            );
        }
    }

    /**
     * Get provider adapter
     *
     * @param string $provider Provider name
     * @return \App\Service\Social\SocialAuthProviderInterface
     * @throws \InvalidArgumentException
     */
    public function getProvider(string $provider): SocialAuthProviderInterface
    {
        if (!isset($this->providers[$provider])) {
            throw new InvalidArgumentException("Unsupported provider: {$provider}");
        }

        return $this->providers[$provider];
    }

    /**
     * Get authorization URL for provider
     *
     * @param string $provider Provider name
     * @param string $redirectUri Callback URL
     * @param array $options Additional options
     * @return string Authorization URL
     * @throws \InvalidArgumentException
     */
    public function getAuthorizationUrl(string $provider, string $redirectUri, array $options = []): string
    {
        $providerAdapter = $this->getProvider($provider);

        return $providerAdapter->getAuthorizationUrl($redirectUri, $options);
    }

    /**
     * Authenticate user with provider
     *
     * @param string $provider Provider name
     * @param string $code Authorization code
     * @param string $redirectUri Callback URL
     * @return \App\Model\Entity\User User entity
     * @throws \InvalidArgumentException
     */
    public function authenticate(string $provider, string $code, string $redirectUri): User
    {
        $providerAdapter = $this->getProvider($provider);

        // Get access token
        $tokenData = $providerAdapter->getAccessToken($code, $redirectUri);

        // Get user profile
        $profileData = $providerAdapter->getUserProfile($tokenData['access_token']);

        // Find or create user
        return $this->findOrCreateUser($provider, $profileData, $tokenData);
    }

    /**
     * Link social account to existing user
     *
     * @param string $provider Provider name
     * @param string $code Authorization code
     * @param string $redirectUri Callback URL
     * @param string $userId Existing user ID
     * @return \App\Model\Entity\User User entity
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function linkAccount(string $provider, string $code, string $redirectUri, string $userId): User
    {
        $providerAdapter = $this->getProvider($provider);

        // Get access token
        $tokenData = $providerAdapter->getAccessToken($code, $redirectUri);

        // Get user profile
        $profileData = $providerAdapter->getUserProfile($tokenData['access_token']);

        /** @var \App\Model\Table\SocialAccountsTable $socialAccountsTable */
        $socialAccountsTable = $this->fetchTable('SocialAccounts');

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        // Check if social account already exists for another user
        $existingSocialAccount = $socialAccountsTable
            ->find('byProviderUser', provider: $provider, providerUserId: $profileData['id'])
            ->first();

        if ($existingSocialAccount && $existingSocialAccount->user_id !== $userId) {
            throw new \RuntimeException('This social account is already linked to another user');
        }

        if ($existingSocialAccount) {
            // Update existing social account tokens
            $existingSocialAccount->access_token = $tokenData['access_token'];
            $existingSocialAccount->refresh_token = $tokenData['refresh_token'];
            if (isset($tokenData['expires_in'])) {
                $existingSocialAccount->expires_at = new \DateTime('+' . $tokenData['expires_in'] . ' seconds');
            }
            $socialAccountsTable->save($existingSocialAccount);

            return $usersTable->get($userId);
        }

        // Create new social account linked to existing user
        $socialAccount = $socialAccountsTable->newEntity([
            'user_id' => $userId,
            'provider' => $provider,
            'provider_user_id' => $profileData['id'],
            'email' => $profileData['email'],
            'name' => $profileData['name'],
            'avatar_url' => $profileData['avatar_url'],
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'],
            'expires_at' => isset($tokenData['expires_in'])
                ? new \DateTime('+' . $tokenData['expires_in'] . ' seconds')
                : null,
            'raw_data' => $profileData['raw'],
        ]);

        if (!$socialAccountsTable->save($socialAccount)) {
            throw new \RuntimeException('Failed to link social account');
        }

        return $usersTable->get($userId);
    }

    /**
     * Find or create user from social profile
     *
     * @param string $provider Provider name
     * @param array $profileData User profile data
     * @param array $tokenData Token data
     * @return \App\Model\Entity\User
     */
    private function findOrCreateUser(string $provider, array $profileData, array $tokenData): User
    {
        /** @var \App\Model\Table\SocialAccountsTable $socialAccountsTable */
        $socialAccountsTable = $this->fetchTable('SocialAccounts');

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        // Find existing social account
        $socialAccount = $socialAccountsTable
            ->find('byProviderUser', provider: $provider, providerUserId: $profileData['id'])
            ->contain(['Users'])
            ->first();

        if ($socialAccount) {
            // Update token data
            $socialAccount->access_token = $tokenData['access_token'];
            $socialAccount->refresh_token = $tokenData['refresh_token'];
            if (isset($tokenData['expires_in'])) {
                $socialAccount->expires_at = new \DateTime('+' . $tokenData['expires_in'] . ' seconds');
            }
            $socialAccountsTable->save($socialAccount);

            return $socialAccount->user;
        }

        // Create new user
        $user = $usersTable->newEntity([
            'username' => $this->generateUniqueUsername($profileData),
            'email' => $profileData['email'],
            'password_hash' => null, // Social login users don't have password
            'is_active' => true,
        ]);

        if (!$usersTable->save($user)) {
            throw new \RuntimeException('Failed to create user');
        }

        // Create social account
        $socialAccount = $socialAccountsTable->newEntity([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_user_id' => $profileData['id'],
            'email' => $profileData['email'],
            'name' => $profileData['name'],
            'avatar_url' => $profileData['avatar_url'],
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'],
            'expires_at' => isset($tokenData['expires_in'])
                ? new \DateTime('+' . $tokenData['expires_in'] . ' seconds')
                : null,
            'raw_data' => $profileData['raw'],
        ]);

        if (!$socialAccountsTable->save($socialAccount)) {
            throw new \RuntimeException('Failed to create social account');
        }

        return $user;
    }

    /**
     * Generate unique username from profile data
     *
     * @param array $profileData Profile data
     * @return string Unique username
     */
    private function generateUniqueUsername(array $profileData): string
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        // Extract base username from email or name
        $baseUsername = $profileData['email'] ? explode('@', $profileData['email'])[0] : 'user';
        $baseUsername = preg_replace('/[^a-zA-Z0-9_]/', '', $baseUsername);

        // Ensure unique
        $username = $baseUsername;
        $counter = 1;
        while ($usersTable->find()->where(['username' => $username])->count() > 0) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
