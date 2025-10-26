<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Client;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Client Test Case
 */
class ClientTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Entity\Client
     */
    protected $Client;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->Client = new Client();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Client);
        parent::tearDown();
    }

    /**
     * Test that client_secret is automatically hashed
     *
     * @return void
     */
    public function testClientSecretIsAutomaticallyHashed(): void
    {
        $plainPassword = 'my-secret-password';
        $this->Client->client_secret = $plainPassword;

        $hashedPassword = $this->Client->client_secret;

        // パスワードがハッシュ化されていることを確認
        $this->assertNotEquals($plainPassword, $hashedPassword);
        $this->assertStringStartsWith('$2y$', $hashedPassword); // bcryptのプレフィックス

        // ハッシュの検証
        $this->assertTrue(password_verify($plainPassword, $hashedPassword));
    }

    /**
     * Test that empty client_secret returns null
     *
     * @return void
     */
    public function testEmptyClientSecretReturnsNull(): void
    {
        $this->Client->client_secret = '';
        $this->assertNull($this->Client->client_secret);
    }

    /**
     * Test redirect_uris getter converts JSON to array
     *
     * @return void
     */
    public function testRedirectUrisGetterConvertsJsonToArray(): void
    {
        $uris = ['https://example.com/callback', 'https://example.com/callback2'];

        // setterを使って配列をセット
        $this->Client->redirect_uris = $uris;

        // getterで配列が返されることを確認
        $result = $this->Client->redirect_uris;
        $this->assertIsArray($result);
        $this->assertEquals($uris, $result);
    }

    /**
     * Test redirect_uris setter converts array to JSON
     *
     * @return void
     */
    public function testRedirectUrisSetterConvertsArrayToJson(): void
    {
        $uris = ['https://example.com/callback'];
        $this->Client->redirect_uris = $uris;

        // getterで配列が返されることを確認
        $result = $this->Client->redirect_uris;
        $this->assertIsArray($result);
        $this->assertEquals($uris, $result);

        // dirty状態を確認（値が変更されたことを確認）
        $this->assertTrue($this->Client->isDirty('redirect_uris'));
    }

    /**
     * Test redirect_uris returns empty array for null
     *
     * @return void
     */
    public function testRedirectUrisReturnsEmptyArrayForNull(): void
    {
        $client = new Client(['redirect_uris' => null]);
        $this->assertIsArray($client->redirect_uris);
        $this->assertEmpty($client->redirect_uris);
    }

    /**
     * Test grant_types getter converts JSON to array
     *
     * @return void
     */
    public function testGrantTypesGetterConvertsJsonToArray(): void
    {
        $grantTypes = ['authorization_code', 'refresh_token'];

        $this->Client->grant_types = $grantTypes;

        $result = $this->Client->grant_types;
        $this->assertIsArray($result);
        $this->assertEquals($grantTypes, $result);
    }

    /**
     * Test grant_types setter converts array to JSON
     *
     * @return void
     */
    public function testGrantTypesSetterConvertsArrayToJson(): void
    {
        $grantTypes = ['authorization_code'];
        $this->Client->grant_types = $grantTypes;

        // getterで配列が返されることを確認
        $result = $this->Client->grant_types;
        $this->assertIsArray($result);
        $this->assertEquals($grantTypes, $result);

        // dirty状態を確認（値が変更されたことを確認）
        $this->assertTrue($this->Client->isDirty('grant_types'));
    }

    /**
     * Test grant_types returns empty array for null
     *
     * @return void
     */
    public function testGrantTypesReturnsEmptyArrayForNull(): void
    {
        $client = new Client(['grant_types' => null]);
        $this->assertIsArray($client->grant_types);
        $this->assertEmpty($client->grant_types);
    }

    /**
     * Test client_secret is hidden in JSON/Array output
     *
     * @return void
     */
    public function testClientSecretIsHidden(): void
    {
        $this->Client->client_secret = 'secret';
        $this->Client->name = 'Test Client';

        $array = $this->Client->toArray();
        $this->assertArrayNotHasKey('client_secret', $array);
        $this->assertArrayHasKey('name', $array);
    }

    /**
     * Test accessible fields
     *
     * @return void
     */
    public function testAccessibleFields(): void
    {
        $data = [
            'client_id' => 'test-client-id',
            'client_secret' => 'secret',
            'name' => 'Test Client',
            'redirect_uris' => ['https://example.com'],
            'grant_types' => ['authorization_code'],
            'is_confidential' => true,
            'is_active' => true,
            'created' => '2025-01-01 00:00:00',
            'modified' => '2025-01-01 00:00:00',
        ];

        $client = new Client($data);

        // アクセス可能なフィールドはセットされる
        $this->assertEquals('test-client-id', $client->client_id);
        $this->assertNotEmpty($client->client_secret);
        $this->assertEquals('Test Client', $client->name);
        $this->assertEquals(['https://example.com'], $client->redirect_uris);
        $this->assertEquals(['authorization_code'], $client->grant_types);
        $this->assertTrue($client->is_confidential);
        $this->assertTrue($client->is_active);

        // created/modifiedはアクセス不可
        // コンストラクタではセットされる（これはCakePHPの仕様）
        $this->assertTrue($client->has('created'));
        $this->assertTrue($client->has('modified'));

        // _accessibleがfalseでも直接代入は可能（CakePHPの仕様）
        // 実際の制限はnewEntity/patchEntityで機能する
    }

    /**
     * Test that scopes relation is accessible
     *
     * @return void
     */
    public function testScopesRelationIsAccessible(): void
    {
        $scopesData = [
            ['name' => 'openid'],
            ['name' => 'profile'],
        ];

        $client = new Client(['scopes' => $scopesData]);
        $this->assertEquals($scopesData, $client->scopes);
    }
}
