<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateAuthorizationCodes extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('authorization_codes', ['id' => false, 'primary_key' => ['id']]);

        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('code', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);

        $table->addColumn('client_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('user_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('redirect_uri', 'string', [
            'default' => null,
            'limit' => 500,
            'null' => false,
        ]);

        $table->addColumn('scopes', 'text', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('expires_at', 'timestamp', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('is_used', 'boolean', [
            'default' => false,
            'null' => false,
        ]);

        $table->addColumn('created', 'timestamp', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);

        $table->addColumn('modified', 'timestamp', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);

        $table->addIndex(['code'], ['unique' => true]);
        $table->addIndex(['client_id']);
        $table->addIndex(['user_id']);
        $table->addIndex(['expires_at']);
        $table->addIndex(['is_used']);

        $table->addForeignKey('client_id', 'clients', 'id', [
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ]);

        $table->addForeignKey('user_id', 'users', 'id', [
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ]);

        $table->create();
    }
}
