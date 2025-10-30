<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateSocialAccounts extends BaseMigration
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
        $table = $this->table('social_accounts', ['id' => false, 'primary_key' => ['id']]);

        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('user_id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('provider', 'string', [
            'default' => null,
            'limit' => 50,
            'null' => false,
        ])
        ->addColumn('provider_user_id', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ])
        ->addColumn('email', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ])
        ->addColumn('name', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ])
        ->addColumn('avatar_url', 'text', [
            'default' => null,
            'null' => true,
        ])
        ->addColumn('access_token_encrypted', 'text', [
            'default' => null,
            'null' => true,
        ])
        ->addColumn('refresh_token_encrypted', 'text', [
            'default' => null,
            'null' => true,
        ])
        ->addColumn('expires_at', 'timestamp', [
            'default' => null,
            'null' => true,
        ])
        ->addColumn('raw_data', 'json', [
            'default' => null,
            'null' => true,
        ])
        ->addColumn('created', 'timestamp', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('modified', 'timestamp', [
            'default' => null,
            'null' => false,
        ])
        ->addIndex(['user_id'], ['name' => 'idx_social_accounts_user_id'])
        ->addIndex(['email'], ['name' => 'idx_social_accounts_email'])
        ->addIndex(['provider', 'provider_user_id'], [
            'name' => 'uniq_provider_user',
            'unique' => true,
        ])
        ->addForeignKey('user_id', 'users', 'id', [
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ])
        ->create();
    }
}
