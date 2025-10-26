<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property string $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property bool $is_active
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'username' => true,
        'email' => true,
        'password' => true,
        'is_active' => true,
        'created' => false,
        'modified' => false,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'password',
        'password_hash',
    ];

    /**
     * Virtual fields that should be exposed in array/JSON representations
     *
     * @var list<string>
     */
    protected array $_virtual = ['password'];

    /**
     * Getter for password returns password_hash value
     *
     * @return string|null
     */
    protected function _getPassword(): ?string
    {
        return $this->_fields['password_hash'] ?? null;
    }

    /**
     * Setter for password that automatically hashes the value
     * and stores it in password_hash field
     *
     * @param string $password Plain text password
     * @return void
     */
    protected function _setPassword(string $password): void
    {
        if (strlen($password) > 0) {
            $this->set('password_hash', password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));
        }
    }
}
