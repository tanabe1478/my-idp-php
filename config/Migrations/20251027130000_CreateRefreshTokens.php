<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * CreateRefreshTokens migration
 *
 * Creates the refresh_tokens table for OAuth2 refresh token management
 */
class CreateRefreshTokens extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('refresh_tokens', ['id' => false, 'primary_key' => ['id']]);

        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('token', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
            'comment' => 'Refresh token string (cryptographically secure)',
        ]);

        $table->addColumn('client_id', 'uuid', [
            'default' => null,
            'null' => false,
            'comment' => 'Client that owns this refresh token',
        ]);

        $table->addColumn('user_id', 'uuid', [
            'default' => null,
            'null' => false,
            'comment' => 'User that authorized this token',
        ]);

        $table->addColumn('scopes', 'text', [
            'default' => null,
            'null' => false,
            'comment' => 'JSON array of granted scopes',
        ]);

        $table->addColumn('expires_at', 'timestamp', [
            'default' => null,
            'null' => false,
            'comment' => 'Token expiration time',
        ]);

        $table->addColumn('is_revoked', 'boolean', [
            'default' => false,
            'null' => false,
            'comment' => 'Whether this token has been revoked',
        ]);

        $table->addColumn('created', 'timestamp', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);

        $table->addColumn('modified', 'timestamp', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);

        // Indexes
        $table->addIndex(['token'], ['unique' => true, 'name' => 'idx_token_unique']);
        $table->addIndex(['client_id'], ['name' => 'idx_client_id']);
        $table->addIndex(['user_id'], ['name' => 'idx_user_id']);
        $table->addIndex(['expires_at'], ['name' => 'idx_expires_at']);
        $table->addIndex(['is_revoked'], ['name' => 'idx_is_revoked']);

        // Foreign keys
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
