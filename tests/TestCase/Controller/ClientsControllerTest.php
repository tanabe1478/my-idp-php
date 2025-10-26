<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ClientsController Test Case
 *
 * @uses \App\Controller\ClientsController
 */
class ClientsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Clients',
        'app.Scopes',
        'app.ClientsScopes',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Disable CSRF protection for integration tests
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        // Login as testuser1 for authenticated requests
        $this->session([
            'Auth' => [
                'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'username' => 'testuser1',
                'email' => 'testuser1@example.com',
            ],
        ]);
    }

    /**
     * Test add method displays form
     *
     * @return void
     * @uses \App\Controller\ClientsController::add()
     */
    public function testAddDisplaysForm(): void
    {
        $this->get('/clients/add');

        $this->assertResponseOk();
        $this->assertResponseContains('name');
        $this->assertResponseContains('redirect_uris');
        $this->assertResponseContains('grant_types');
    }

    /**
     * Test add method with valid data
     *
     * @return void
     * @uses \App\Controller\ClientsController::add()
     */
    public function testAddWithValidData(): void
    {
        $data = [
            'name' => 'Test Client Application',
            'redirect_uris' => [
                'https://example.com/callback',
                'https://example.com/callback2',
            ],
            'grant_types' => [
                'authorization_code',
                'refresh_token',
            ],
            'is_confidential' => true,
            'is_active' => true,
        ];

        $this->post('/clients/add', $data);

        $this->assertResponseSuccess();
        $this->assertRedirectContains('/clients/view/');

        // Verify client was created
        $clients = $this->getTableLocator()->get('Clients');
        $query = $clients->find()->where(['name' => 'Test Client Application']);
        $this->assertEquals(1, $query->count());

        $client = $query->first();
        $this->assertNotNull($client->client_id);
        $this->assertNotNull($client->client_secret);
        $this->assertEquals('Test Client Application', $client->name);
        $this->assertEquals(['https://example.com/callback', 'https://example.com/callback2'], $client->redirect_uris);
        $this->assertEquals(['authorization_code', 'refresh_token'], $client->grant_types);
        $this->assertTrue($client->is_confidential);
        $this->assertTrue($client->is_active);
    }

    /**
     * Test add method without name
     *
     * @return void
     * @uses \App\Controller\ClientsController::add()
     */
    public function testAddWithoutName(): void
    {
        $data = [
            'name' => '',
            'redirect_uris' => ['https://example.com/callback'],
            'grant_types' => ['authorization_code'],
            'is_confidential' => true,
            'is_active' => true,
        ];

        $this->post('/clients/add', $data);

        $this->assertResponseOk();
        $this->assertResponseContains('name');

        // Verify client was not created
        $clients = $this->getTableLocator()->get('Clients');
        $this->assertEquals(3, $clients->find()->count()); // Only fixture data
    }

    /**
     * Test add method with empty redirect_uris
     *
     * @return void
     * @uses \App\Controller\ClientsController::add()
     */
    public function testAddWithEmptyRedirectUris(): void
    {
        $data = [
            'name' => 'Test Client',
            'redirect_uris' => [],
            'grant_types' => ['authorization_code'],
            'is_confidential' => true,
            'is_active' => true,
        ];

        $this->post('/clients/add', $data);

        $this->assertResponseOk();
        $this->assertResponseContains('redirect_uris');

        // Verify client was not created
        $clients = $this->getTableLocator()->get('Clients');
        $this->assertEquals(3, $clients->find()->count()); // Only fixture data
    }

    /**
     * Test add method with empty grant_types
     *
     * @return void
     * @uses \App\Controller\ClientsController::add()
     */
    public function testAddWithEmptyGrantTypes(): void
    {
        $data = [
            'name' => 'Test Client',
            'redirect_uris' => ['https://example.com/callback'],
            'grant_types' => [],
            'is_confidential' => true,
            'is_active' => true,
        ];

        $this->post('/clients/add', $data);

        $this->assertResponseOk();
        $this->assertResponseContains('grant_types');

        // Verify client was not created
        $clients = $this->getTableLocator()->get('Clients');
        $this->assertEquals(3, $clients->find()->count()); // Only fixture data
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\ClientsController::view()
     */
    public function testViewClient(): void
    {
        $this->get('/clients/view/11111111-1111-1111-1111-111111111111');

        $this->assertResponseOk();
        $this->assertResponseContains('Test Client 1');
        $this->assertResponseContains('test_client_1');
    }

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\ClientsController::index()
     */
    public function testIndexClients(): void
    {
        $this->get('/clients/index');

        $this->assertResponseOk();
        $this->assertResponseContains('Test Client 1');
        $this->assertResponseContains('Test Client 2');
    }

    /**
     * Test unauthenticated user cannot access add
     *
     * @return void
     * @uses \App\Controller\ClientsController::add()
     */
    public function testUnauthenticatedUserCannotAccessAdd(): void
    {
        // Clean up any previous request state
        $this->cleanup();

        // Make a request without setting up authentication session
        $this->get('/clients/add');

        $this->assertRedirect();
        $this->assertRedirectContains('/users/login');
    }
}
