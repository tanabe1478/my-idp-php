<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ClientsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ClientsTable Test Case
 */
class ClientsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ClientsTable
     */
    protected $Clients;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Clients',
        'app.Scopes',
        'app.ClientsScopes',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Clients') ? [] : ['className' => ClientsTable::class];
        $this->Clients = TableRegistry::getTableLocator()->get('Clients', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Clients);
        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertInstanceOf(ClientsTable::class, $this->Clients);
        $this->assertEquals('clients', $this->Clients->getTable());
        $this->assertEquals('name', $this->Clients->getDisplayField());
        $this->assertEquals('id', $this->Clients->getPrimaryKey());

        // Timestampビヘイビアが追加されているか確認
        $this->assertTrue($this->Clients->hasBehavior('Timestamp'));
    }

    /**
     * Test belongsToMany association with Scopes
     *
     * @return void
     */
    public function testBelongsToManyScopes(): void
    {
        $this->assertTrue($this->Clients->associations()->has('Scopes'));

        $association = $this->Clients->associations()->get('Scopes');
        $this->assertEquals('manyToMany', $association->type());
        $this->assertEquals('Scopes', $association->getName());
        $this->assertEquals('client_id', $association->getForeignKey());
        $this->assertEquals('scope_id', $association->getTargetForeignKey());
        $this->assertEquals('clients_scopes', $association->junction()->getTable());
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        // 有効なデータ
        $data = [
            'client_id' => 'test-client-' . uniqid(),
            'name' => 'Test Client',
            'redirect_uris' => ['https://example.com/callback'],
            'grant_types' => ['authorization_code'],
            'is_confidential' => true,
            'is_active' => true,
        ];

        $client = $this->Clients->newEntity($data);
        $this->assertEmpty($client->getErrors(), 'Valid data should not have errors');
    }

    /**
     * Test validation fails when required fields are missing
     *
     * @return void
     */
    public function testValidationRequiredFields(): void
    {
        $client = $this->Clients->newEntity([]);
        $errors = $client->getErrors();

        $this->assertArrayHasKey('client_id', $errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('redirect_uris', $errors);
        $this->assertArrayHasKey('grant_types', $errors);
    }

    /**
     * Test validation for client_id max length
     *
     * @return void
     */
    public function testValidationClientIdMaxLength(): void
    {
        $data = [
            'client_id' => str_repeat('a', 256), // 256文字（制限は255）
            'name' => 'Test Client',
            'redirect_uris' => ['https://example.com/callback'],
            'grant_types' => ['authorization_code'],
        ];

        $client = $this->Clients->newEntity($data);
        $errors = $client->getErrors();

        $this->assertArrayHasKey('client_id', $errors);
    }

    /**
     * Test validation for name max length
     *
     * @return void
     */
    public function testValidationNameMaxLength(): void
    {
        $data = [
            'client_id' => 'test-client',
            'name' => str_repeat('a', 256), // 256文字（制限は255）
            'redirect_uris' => ['https://example.com/callback'],
            'grant_types' => ['authorization_code'],
        ];

        $client = $this->Clients->newEntity($data);
        $errors = $client->getErrors();

        $this->assertArrayHasKey('name', $errors);
    }

    /**
     * Test validation for unique client_id
     *
     * @return void
     */
    public function testValidationUniqueClientId(): void
    {
        // 最初のクライアントを保存
        $client1 = $this->Clients->newEntity([
            'client_id' => 'duplicate-client-id',
            'name' => 'Client 1',
            'redirect_uris' => ['https://example.com/callback'],
            'grant_types' => ['authorization_code'],
        ]);
        $this->Clients->save($client1);

        // 同じclient_idで2番目のクライアントを作成
        $client2 = $this->Clients->newEntity([
            'client_id' => 'duplicate-client-id',
            'name' => 'Client 2',
            'redirect_uris' => ['https://example.com/callback2'],
            'grant_types' => ['authorization_code'],
        ]);

        // 保存に失敗することを確認
        $result = $this->Clients->save($client2);
        $this->assertFalse($result);

        $errors = $client2->getErrors();
        $this->assertArrayHasKey('client_id', $errors);
    }

    /**
     * Test findByClientId custom finder
     *
     * @return void
     */
    public function testFindByClientId(): void
    {
        // テストデータを作成
        $clientId = 'test-find-client-' . uniqid();
        $client = $this->Clients->newEntity([
            'client_id' => $clientId,
            'name' => 'Test Find Client',
            'redirect_uris' => ['https://example.com/callback'],
            'grant_types' => ['authorization_code'],
        ]);
        $this->Clients->save($client);

        // findByClientIdで検索
        $found = $this->Clients->findByClientId($clientId);

        $this->assertNotNull($found);
        $this->assertEquals($clientId, $found->client_id);
        $this->assertEquals('Test Find Client', $found->name);

        // Scopesが含まれているか確認（containが機能しているか）
        $this->assertTrue($found->has('scopes'), 'Scopes property should be populated by contain');
    }

    /**
     * Test findByClientId returns null for non-existent client
     *
     * @return void
     */
    public function testFindByClientIdNotFound(): void
    {
        $found = $this->Clients->findByClientId('non-existent-client-id');
        $this->assertNull($found);
    }

    /**
     * Test saving client with scopes association
     *
     * @return void
     */
    public function testSaveWithScopes(): void
    {
        $Scopes = TableRegistry::getTableLocator()->get('Scopes');
        $scope = $Scopes->find()->where(['name' => 'openid'])->first();

        $client = $this->Clients->newEntity([
            'client_id' => 'test-with-scopes-' . uniqid(),
            'name' => 'Test Client with Scopes',
            'redirect_uris' => ['https://example.com/callback'],
            'grant_types' => ['authorization_code'],
            'scopes' => [
                '_ids' => [$scope->id],
            ],
        ]);

        $result = $this->Clients->save($client);
        $this->assertNotFalse($result);

        // 関連付けが保存されたか確認
        $saved = $this->Clients->get($client->id, contain: ['Scopes']);
        $this->assertCount(1, $saved->scopes);
        $this->assertEquals('openid', $saved->scopes[0]->name);
    }

    /**
     * Test Timestamp behavior sets created and modified
     *
     * @return void
     */
    public function testTimestampBehavior(): void
    {
        $client = $this->Clients->newEntity([
            'client_id' => 'test-timestamp-' . uniqid(),
            'name' => 'Test Timestamp',
            'redirect_uris' => ['https://example.com/callback'],
            'grant_types' => ['authorization_code'],
        ]);

        $saved = $this->Clients->save($client);

        $this->assertNotNull($saved->created);
        $this->assertNotNull($saved->modified);
        $this->assertInstanceOf(\Cake\I18n\DateTime::class, $saved->created);
        $this->assertInstanceOf(\Cake\I18n\DateTime::class, $saved->modified);
    }
}
