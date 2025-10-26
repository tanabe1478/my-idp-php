<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AuthorizationCode Entity
 *
 * @property string $id
 * @property string $code
 * @property string $client_id
 * @property string $user_id
 * @property string $redirect_uri
 * @property array $scopes
 * @property \Cake\I18n\DateTime $expires_at
 * @property bool $is_used
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Client $client
 * @property \App\Model\Entity\User $user
 */
class AuthorizationCode extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'code' => true,
        'client_id' => true,
        'user_id' => true,
        'redirect_uri' => true,
        'scopes' => true,
        'expires_at' => true,
        'is_used' => true,
        'client' => true,
        'user' => true,
        'created' => false,
        'modified' => false,
    ];

    /**
     * Getter for scopes that converts null or JSON string to array
     *
     * @param array|string|null $value Array from JSON type, JSON string, or null
     * @return array Array of scopes
     */
    protected function _getScopes($value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }

        return $value ?? [];
    }

    /**
     * Check if authorization code is expired
     *
     * @return bool True if expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < new \DateTime();
    }

    /**
     * Check if authorization code is valid (not expired and not used)
     *
     * @return bool True if valid
     */
    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }
}
