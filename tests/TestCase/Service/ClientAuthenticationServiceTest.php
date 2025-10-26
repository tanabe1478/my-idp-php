<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\ClientAuthenticationService;
use Cake\TestSuite\TestCase;

/**
 * App\Service\ClientAuthenticationService Test Case
 */
class ClientAuthenticationServiceTest extends TestCase
{
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
     * Test subject
     *
     * @var \App\Service\ClientAuthenticationService
     */
    protected ClientAuthenticationService $service;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $clientsTable = $this->getTableLocator()->get('Clients');
        $this->service = new ClientAuthenticationService($clientsTable);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->service);
        parent::tearDown();
    }

    /**
     * Test authenticate with valid credentials
     *
     * @return void
     */
    public function testAuthenticateWithValidCredentials(): void
    {
        // Fixture has client_id 'test_client_1' with known secret 'secret'
        $client = $this->service->authenticate('test_client_1', 'secret');

        $this->assertNotNull($client);
        $this->assertEquals('test_client_1', $client->client_id);
        $this->assertEquals('Test Client 1', $client->name);
        $this->assertTrue($client->is_confidential);
        $this->assertTrue($client->is_active);
    }

    /**
     * Test authenticate with invalid client_id
     *
     * @return void
     */
    public function testAuthenticateWithInvalidClientId(): void
    {
        $client = $this->service->authenticate('invalid_client_id', 'secret');

        $this->assertNull($client);
    }

    /**
     * Test authenticate with invalid client_secret
     *
     * @return void
     */
    public function testAuthenticateWithInvalidClientSecret(): void
    {
        $client = $this->service->authenticate('test_client_1', 'wrong_secret');

        $this->assertNull($client);
    }

    /**
     * Test authenticate with inactive client
     *
     * @return void
     */
    public function testAuthenticateWithInactiveClient(): void
    {
        // Fixture has client_id 'test_client_3' which is inactive
        $client = $this->service->authenticate('test_client_3', 'secret');

        $this->assertNull($client);
    }

    /**
     * Test authenticate public client (no secret required)
     *
     * @return void
     */
    public function testAuthenticatePublicClient(): void
    {
        // Public clients (is_confidential = false) should authenticate without secret
        // Fixture has 'test_client_2' which is public (is_confidential = false)
        $client = $this->service->authenticate('test_client_2', '');

        $this->assertNotNull($client);
        $this->assertEquals('test_client_2', $client->client_id);
        $this->assertEquals('Test Client 2', $client->name);
        $this->assertFalse($client->is_confidential);
        $this->assertTrue($client->is_active);
    }

    /**
     * Test confidential client requires secret
     *
     * @return void
     */
    public function testConfidentialClientRequiresSecret(): void
    {
        // Confidential clients must provide a secret
        $client = $this->service->authenticate('test_client_1', '');

        $this->assertNull($client);
    }

    /**
     * Test authenticate with null secret
     *
     * @return void
     */
    public function testAuthenticateWithNullSecret(): void
    {
        $client = $this->service->authenticate('test_client_1', null);

        $this->assertNull($client);
    }

    /**
     * Test authenticate returns client with scopes
     *
     * @return void
     */
    public function testAuthenticateReturnsClientWithScopes(): void
    {
        $client = $this->service->authenticate('test_client_1', 'secret');

        $this->assertNotNull($client);
        $this->assertIsArray($client->scopes);
        $this->assertNotEmpty($client->scopes);
        $this->assertEquals('openid', $client->scopes[0]->name);
    }
}
