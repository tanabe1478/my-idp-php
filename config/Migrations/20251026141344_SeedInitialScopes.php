<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Ramsey\Uuid\Uuid;

class SeedInitialScopes extends BaseMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $table = $this->table('scopes');

        $data = [
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'openid',
                'description' => 'OpenID Connect base scope',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'profile',
                'description' => 'Access to profile information (name, picture, etc.)',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'email',
                'description' => 'Access to email address',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
        ];

        $table->insert($data)->save();
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $this->execute("DELETE FROM scopes WHERE name IN ('openid', 'profile', 'email')");
    }
}
