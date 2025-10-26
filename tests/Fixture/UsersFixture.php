<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'username' => 'testuser1',
                'email' => 'testuser1@example.com',
                // パスワード: "password123"
                'password_hash' => '$2y$12$FL.ZlFBAJkwwuIU2Bdtf8OQ9tiSVAUVhXsDYh4CBkFec.4VUlbeJ2',
                'is_active' => true,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                'username' => 'testuser2',
                'email' => 'testuser2@example.com',
                // パスワード: "password456"
                'password_hash' => '$2y$12$4GHmwGbFxPwnsO9xCy4r/.2PTg/1qcC/LiYuDIIy9jKdmfL0i4iDW',
                'is_active' => true,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'username' => 'inactiveuser',
                'email' => 'inactive@example.com',
                // パスワード: "password789"
                'password_hash' => '$2y$12$yzzUX7Ob69IYFBW9DS.YnOn1rPVu6c6frBznCz2HHSwRSZFRgXbZG',
                'is_active' => false,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
