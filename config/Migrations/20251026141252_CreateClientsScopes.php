<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateClientsScopes extends BaseMigration
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
        $table = $this->table('clients_scopes', ['id' => false, 'primary_key' => ['id']]);

        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('client_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('scope_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);

        $table->addColumn('created', 'timestamp', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);

        $table->addIndex(['client_id', 'scope_id'], ['unique' => true]);
        $table->addIndex(['client_id']);
        $table->addIndex(['scope_id']);

        $table->addForeignKey('client_id', 'clients', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);

        $table->addForeignKey('scope_id', 'scopes', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);

        $table->create();
    }
}
