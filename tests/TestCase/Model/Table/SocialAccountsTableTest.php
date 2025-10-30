<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SocialAccountsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SocialAccountsTable Test Case
 */
class SocialAccountsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SocialAccountsTable
     */
    protected $SocialAccounts;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.SocialAccounts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('SocialAccounts') ? [] : ['className' => SocialAccountsTable::class];
        $this->SocialAccounts = $this->getTableLocator()->get('SocialAccounts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SocialAccounts);
        parent::tearDown();
    }

    /**
     * Test validationDefault
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $validator = $this->SocialAccounts->validationDefault(new \Cake\Validation\Validator());

        $this->assertTrue($validator->hasField('user_id'));
        $this->assertTrue($validator->hasField('provider'));
        $this->assertTrue($validator->hasField('provider_user_id'));
    }

    /**
     * Test validation fails when required fields are missing
     *
     * @return void
     */
    public function testValidationFailsWithMissingRequiredFields(): void
    {
        $socialAccount = $this->SocialAccounts->newEntity([]);
        $this->assertFalse($this->SocialAccounts->save($socialAccount));

        $errors = $socialAccount->getErrors();
        $this->assertArrayHasKey('user_id', $errors);
        $this->assertArrayHasKey('provider', $errors);
        $this->assertArrayHasKey('provider_user_id', $errors);
    }

    /**
     * Test saving a valid social account
     *
     * @return void
     */
    public function testSaveValidSocialAccount(): void
    {
        $data = [
            'id' => '550e8400-e29b-41d4-a716-446655440999',
            'user_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'provider' => 'google',
            'provider_user_id' => 'google_user_123',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'access_token' => 'access_token_value',
            'expires_at' => new \DateTime('+1 hour'),
            'raw_data' => ['key' => 'value'],
        ];

        $socialAccount = $this->SocialAccounts->newEntity($data);
        $saved = $this->SocialAccounts->save($socialAccount);

        $this->assertNotFalse($saved);
        $this->assertNotEmpty($saved->id);
        $this->assertEquals($data['provider'], $saved->provider);
    }

    /**
     * Test findByProviderUser custom finder
     *
     * @return void
     */
    public function testFindByProviderUser(): void
    {
        $result = $this->SocialAccounts->find('byProviderUser', provider: 'google', providerUserId: 'google_test_user_1')->first();

        $this->assertNotNull($result);
        $this->assertEquals('google', $result->provider);
        $this->assertEquals('google_test_user_1', $result->provider_user_id);
    }

    /**
     * Test findByProviderUser returns null when not found
     *
     * @return void
     */
    public function testFindByProviderUserReturnsNullWhenNotFound(): void
    {
        $result = $this->SocialAccounts->find('byProviderUser', provider: 'google', providerUserId: 'nonexistent_user')->first();

        $this->assertNull($result);
    }

    /**
     * Test belongsTo Users association
     *
     * @return void
     */
    public function testBelongsToUsersAssociation(): void
    {
        $socialAccount = $this->SocialAccounts->get('550e8400-e29b-41d4-a716-446655440001', contain: ['Users']);

        $this->assertNotNull($socialAccount->user);
        $this->assertEquals('testuser1', $socialAccount->user->username);
    }

    /**
     * Test unique constraint on provider and provider_user_id
     *
     * @return void
     */
    public function testUniqueProviderUserIdConstraint(): void
    {
        // First social account
        $data1 = [
            'id' => '550e8400-e29b-41d4-a716-446655440888',
            'user_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'provider' => 'google',
            'provider_user_id' => 'unique_user_123',
            'email' => 'test1@example.com',
        ];

        $socialAccount1 = $this->SocialAccounts->newEntity($data1);
        $this->assertNotFalse($this->SocialAccounts->save($socialAccount1));

        // Duplicate provider + provider_user_id should fail
        $data2 = [
            'id' => '550e8400-e29b-41d4-a716-446655440777',
            'user_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
            'provider' => 'google',
            'provider_user_id' => 'unique_user_123',
            'email' => 'test2@example.com',
        ];

        $socialAccount2 = $this->SocialAccounts->newEntity($data2);
        $saved = $this->SocialAccounts->save($socialAccount2);

        // CakePHP should detect the unique constraint violation
        $this->assertFalse($saved);
    }
}
