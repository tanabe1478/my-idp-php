<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Security;

/**
 * SocialAccount Entity
 *
 * @property string $id
 * @property string $user_id
 * @property string $provider
 * @property string $provider_user_id
 * @property string|null $email
 * @property string|null $name
 * @property string|null $avatar_url
 * @property string|null $access_token_encrypted
 * @property string|null $refresh_token_encrypted
 * @property \Cake\I18n\DateTime|null $expires_at
 * @property array|null $raw_data
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\User $user
 */
class SocialAccount extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'id' => true,
        'user_id' => true,
        'provider' => true,
        'provider_user_id' => true,
        'email' => true,
        'name' => true,
        'avatar_url' => true,
        'access_token' => true,
        'refresh_token' => true,
        'expires_at' => true,
        'raw_data' => true,
        'user' => true,
        'created' => false,
        'modified' => false,
    ];

    /**
     * Fields that are excluded from JSON serialization
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'access_token_encrypted',
        'refresh_token_encrypted',
    ];

    /**
     * Virtual fields
     *
     * @var array<string>
     */
    protected array $_virtual = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Encrypt and set access token
     *
     * @param string|null $token Plain text token
     * @return string|null Encrypted token
     */
    protected function _setAccessToken(?string $token): ?string
    {
        if ($token === null) {
            return null;
        }

        $encrypted = Security::encrypt($token, Security::getSalt());
        $this->_fields['access_token_encrypted'] = base64_encode($encrypted);

        return $token;
    }

    /**
     * Decrypt and get access token
     *
     * @return string|null Decrypted token
     */
    protected function _getAccessToken(): ?string
    {
        if (empty($this->access_token_encrypted)) {
            return null;
        }

        $encrypted = base64_decode($this->access_token_encrypted);
        $decrypted = Security::decrypt($encrypted, Security::getSalt());

        return $decrypted !== false ? $decrypted : null;
    }

    /**
     * Encrypt and set refresh token
     *
     * @param string|null $token Plain text token
     * @return string|null Encrypted token
     */
    protected function _setRefreshToken(?string $token): ?string
    {
        if ($token === null) {
            return null;
        }

        $encrypted = Security::encrypt($token, Security::getSalt());
        $this->_fields['refresh_token_encrypted'] = base64_encode($encrypted);

        return $token;
    }

    /**
     * Decrypt and get refresh token
     *
     * @return string|null Decrypted token
     */
    protected function _getRefreshToken(): ?string
    {
        if (empty($this->refresh_token_encrypted)) {
            return null;
        }

        $encrypted = base64_decode($this->refresh_token_encrypted);
        $decrypted = Security::decrypt($encrypted, Security::getSalt());

        return $decrypted !== false ? $decrypted : null;
    }

    /**
     * Check if access token is expired
     *
     * @return bool True if expired
     */
    public function isTokenExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return $this->expires_at < new \DateTime();
    }
}
