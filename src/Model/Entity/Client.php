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
     * Getter for redirect_uris that converts JSON to array
     *
     * @param string|null $value JSON string from database
     * @return array Array of redirect URIs
     */
    protected function _getRedirectUris(?string $value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Setter for redirect_uris that converts array to JSON
     *
     * @param array|null $value Array of redirect URIs or null
     * @return string|null JSON string for database storage or null
     */
    protected function _setRedirectUris(?array $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value);
    }

    /**
     * Getter for grant_types that converts JSON to array
     *
     * @param string|null $value JSON string from database
     * @return array Array of grant types
     */
    protected function _getGrantTypes(?string $value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Setter for grant_types that converts array to JSON
     *
     * @param array|null $value Array of grant types or null
     * @return string|null JSON string for database storage or null
     */
    protected function _setGrantTypes(?array $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value);
    }
}
