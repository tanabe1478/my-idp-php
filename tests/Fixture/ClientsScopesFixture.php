<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ClientsScopesFixture
 */
class ClientsScopesFixture extends TestFixture
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
                'id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'client_id' => '11111111-1111-1111-1111-111111111111',
                'scope_id' => '11111111-1111-1111-1111-111111111111',
                'created' => '2025-01-01 00:00:00',
            ],
            [
                'id' => 'dddddddd-dddd-dddd-dddd-dddddddddddd',
                'client_id' => '11111111-1111-1111-1111-111111111111',
                'scope_id' => '22222222-2222-2222-2222-222222222222',
                'created' => '2025-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
