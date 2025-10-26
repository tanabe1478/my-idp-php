<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Users') ? [] : ['className' => UsersTable::class];
        $this->Users = TableRegistry::getTableLocator()->get('Users', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Users);
        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertInstanceOf(UsersTable::class, $this->Users);
        $this->assertEquals('users', $this->Users->getTable());
        $this->assertEquals('username', $this->Users->getDisplayField());
        $this->assertEquals('id', $this->Users->getPrimaryKey());

        // Timestampビヘイビアが追加されているか確認
        $this->assertTrue($this->Users->hasBehavior('Timestamp'));
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
            'username' => 'testuser' . uniqid(),
            'email' => 'test' . uniqid() . '@example.com',
            'password' => 'SecurePassword123!',
            'is_active' => true,
        ];

        $user = $this->Users->newEntity($data);
        $this->assertEmpty($user->getErrors(), 'Valid data should not have errors');
    }

    /**
     * Test validation fails when required fields are missing
     *
     * @return void
     */
    public function testValidationRequiredFields(): void
    {
        $user = $this->Users->newEntity([]);
        $errors = $user->getErrors();

        $this->assertArrayHasKey('username', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    /**
     * Test validation for username max length
     *
     * @return void
     */
    public function testValidationUsernameMaxLength(): void
    {
        $data = [
            'username' => str_repeat('a', 256), // 256文字（制限は255）
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $user = $this->Users->newEntity($data);
        $errors = $user->getErrors();

        $this->assertArrayHasKey('username', $errors);
    }

    /**
     * Test validation for email max length
     *
     * @return void
     */
    public function testValidationEmailMaxLength(): void
    {
        $data = [
            'username' => 'testuser',
            'email' => str_repeat('a', 246) . '@example.com', // 256文字超
            'password' => 'password',
        ];

        $user = $this->Users->newEntity($data);
        $errors = $user->getErrors();

        $this->assertArrayHasKey('email', $errors);
    }

    /**
     * Test validation for email format
     *
     * @return void
     */
    public function testValidationEmailFormat(): void
    {
        $data = [
            'username' => 'testuser',
            'email' => 'invalid-email',
            'password' => 'password',
        ];

        $user = $this->Users->newEntity($data);
        $errors = $user->getErrors();

        $this->assertArrayHasKey('email', $errors);
    }

    /**
     * Test validation for unique username
     *
     * @return void
     */
    public function testValidationUniqueUsername(): void
    {
        // 最初のユーザーを保存
        $user1 = $this->Users->newEntity([
            'username' => 'duplicate-username',
            'email' => 'user1@example.com',
            'password' => 'password',
        ]);
        $this->Users->save($user1);

        // 同じusernameで2番目のユーザーを作成
        $user2 = $this->Users->newEntity([
            'username' => 'duplicate-username',
            'email' => 'user2@example.com',
            'password' => 'password',
        ]);

        // 保存に失敗することを確認
        $result = $this->Users->save($user2);
        $this->assertFalse($result);

        $errors = $user2->getErrors();
        $this->assertArrayHasKey('username', $errors);
    }

    /**
     * Test validation for unique email
     *
     * @return void
     */
    public function testValidationUniqueEmail(): void
    {
        // 最初のユーザーを保存
        $user1 = $this->Users->newEntity([
            'username' => 'user1',
            'email' => 'duplicate@example.com',
            'password' => 'password',
        ]);
        $this->Users->save($user1);

        // 同じemailで2番目のユーザーを作成
        $user2 = $this->Users->newEntity([
            'username' => 'user2',
            'email' => 'duplicate@example.com',
            'password' => 'password',
        ]);

        // 保存に失敗することを確認
        $result = $this->Users->save($user2);
        $this->assertFalse($result);

        $errors = $user2->getErrors();
        $this->assertArrayHasKey('email', $errors);
    }

    /**
     * Test password is hashed when saving
     *
     * @return void
     */
    public function testPasswordIsHashed(): void
    {
        $plainPassword = 'MySecurePassword123!';
        $user = $this->Users->newEntity([
            'username' => 'testuser' . uniqid(),
            'email' => 'test' . uniqid() . '@example.com',
            'password' => $plainPassword,
        ]);

        $saved = $this->Users->save($user);
        $this->assertNotFalse($saved);

        // パスワードがハッシュ化されているか確認
        $this->assertNotEquals($plainPassword, $saved->password);
        $this->assertTrue(password_verify($plainPassword, $saved->password));
    }

    /**
     * Test findByUsername custom finder
     *
     * @return void
     */
    public function testFindByUsername(): void
    {
        // テストデータを作成
        $username = 'test-find-user-' . uniqid();
        $user = $this->Users->newEntity([
            'username' => $username,
            'email' => 'test' . uniqid() . '@example.com',
            'password' => 'password',
        ]);
        $this->Users->save($user);

        // findByUsernameで検索
        $found = $this->Users->findByUsername($username);

        $this->assertNotNull($found);
        $this->assertEquals($username, $found->username);
    }

    /**
     * Test findByUsername returns null for non-existent user
     *
     * @return void
     */
    public function testFindByUsernameNotFound(): void
    {
        $found = $this->Users->findByUsername('non-existent-username');
        $this->assertNull($found);
    }

    /**
     * Test findByEmail custom finder
     *
     * @return void
     */
    public function testFindByEmail(): void
    {
        // テストデータを作成
        $email = 'test-find-' . uniqid() . '@example.com';
        $user = $this->Users->newEntity([
            'username' => 'testuser' . uniqid(),
            'email' => $email,
            'password' => 'password',
        ]);
        $this->Users->save($user);

        // findByEmailで検索
        $found = $this->Users->findByEmail($email);

        $this->assertNotNull($found);
        $this->assertEquals($email, $found->email);
    }

    /**
     * Test findByEmail returns null for non-existent user
     *
     * @return void
     */
    public function testFindByEmailNotFound(): void
    {
        $found = $this->Users->findByEmail('non-existent@example.com');
        $this->assertNull($found);
    }

    /**
     * Test Timestamp behavior sets created and modified
     *
     * @return void
     */
    public function testTimestampBehavior(): void
    {
        $user = $this->Users->newEntity([
            'username' => 'test-timestamp-' . uniqid(),
            'email' => 'test' . uniqid() . '@example.com',
            'password' => 'password',
        ]);

        $saved = $this->Users->save($user);

        $this->assertNotNull($saved->created);
        $this->assertNotNull($saved->modified);
        $this->assertInstanceOf(\Cake\I18n\DateTime::class, $saved->created);
        $this->assertInstanceOf(\Cake\I18n\DateTime::class, $saved->modified);
    }
}
