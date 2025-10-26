<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RefreshToken Entity
 *
 * @property string $id
 * @property string $token
 * @property string $client_id
 * @property string $user_id
 * @property array $scopes
 * @property \Cake\I18n\DateTime $expires_at
 * @property bool $is_revoked
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Client $client
 * @property \App\Model\Entity\User $user
 */
class RefreshToken extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'token' => true,
        'client_id' => true,
        'user_id' => true,
        'scopes' => true,
        'expires_at' => true,
        'is_revoked' => true,
        'created' => true,
        'modified' => true,
        'client' => true,
        'user' => true,
    ];

    /**
     * Get scopes as array
     *
     * Converts JSON string to array if necessary
     *
     * @param mixed $value Scopes value (JSON string or array)
     * @return array
     */
    protected function _getScopes($value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }

        return $value ?? [];
    }

    /**
     * Check if the refresh token is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at < new \DateTime();
    }

    /**
     * Check if the refresh token is valid
     *
     * Valid means: not expired and not revoked
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->is_revoked && !$this->isExpired();
    }
}
