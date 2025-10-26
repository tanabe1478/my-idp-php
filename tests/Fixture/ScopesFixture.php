<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ScopesFixture
 */
class ScopesFixture extends TestFixture
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
                'id' => '11111111-1111-1111-1111-111111111111',
                'name' => 'openid',
                'description' => 'OpenID Connect base scope',
                'is_active' => true,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'name' => 'profile',
                'description' => 'Access to profile information',
                'is_active' => true,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => '33333333-3333-3333-3333-333333333333',
                'name' => 'email',
                'description' => 'Access to email address',
                'is_active' => true,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
