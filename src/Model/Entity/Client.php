<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Client Entity
 *
 * @property string $id
 * @property string $client_id
 * @property string|null $client_secret
 * @property string $name
 * @property array $redirect_uris
 * @property array $grant_types
 * @property bool $is_confidential
 * @property bool $is_active
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Scope[] $scopes
 */
class Client extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'client_id' => true,
        'client_secret' => true,
        'name' => true,
        'redirect_uris' => true,
        'grant_types' => true,
        'is_confidential' => true,
        'is_active' => true,
        'scopes' => true,
        'created' => false,
        'modified' => false,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'client_secret',
    ];

    /**
     * Setter for client_secret that automatically hashes the value
     *
     * @param string $password Plain text password
     * @return string|null Hashed password or null for empty strings
     */
    protected function _setClientSecret(string $password): ?string
    {
        if (strlen($password) > 0) {
            return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        return null;
    }

    /**
     * Getter for redirect_uris that converts null or JSON string to array
     *
     * @param array|string|null $value Array from JSON type, JSON string, or null
     * @return array Array of redirect URIs
     */
    protected function _getRedirectUris($value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }

        return $value ?? [];
    }

    /**
     * Getter for grant_types that converts null or JSON string to array
     *
     * @param array|string|null $value Array from JSON type, JSON string, or null
     * @return array Array of grant types
     */
    protected function _getGrantTypes($value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }

        return $value ?? [];
    }
}
