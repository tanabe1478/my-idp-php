<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPkceToAuthorizationCodes extends BaseMigration
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
        $table = $this->table('authorization_codes');
        $table->addColumn('code_challenge', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('code_challenge_method', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addIndex([
            'code_challenge',
        
            ], [
            'name' => 'BY_CODE_CHALLENGE',
            'unique' => false,
        ]);
        $table->addIndex([
            'code_challenge_method',
        
            ], [
            'name' => 'BY_CODE_CHALLENGE_METHOD',
            'unique' => false,
        ]);
        $table->update();
    }
}
