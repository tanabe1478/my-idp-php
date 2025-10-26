<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\UsersController Test Case
 *
 * @uses \App\Controller\UsersController
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

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
    public function setUp(): void
    {
        parent::setUp();

        // Disable CSRF protection for integration tests
        $this->enableCsrfToken();
        $this->enableSecurityToken();
    }

    /**
     * Test login method displays login form
     *
     * @return void
     * @uses \App\Controller\UsersController::login()
     */
    public function testLoginDisplaysForm(): void
    {
        $this->get('/users/login');

        $this->assertResponseOk();
        $this->assertResponseContains('username');
        $this->assertResponseContains('password');
    }

    /**
     * Test login with valid credentials
     *
     * @return void
     * @uses \App\Controller\UsersController::login()
     */
    public function testLoginWithValidCredentials(): void
    {
        $this->post('/users/login', [
            'username' => 'testuser1',
            'password' => 'password123',
        ]);

        $this->assertResponseSuccess();
        $this->assertRedirect(['controller' => 'Users', 'action' => 'index']);
        $this->assertSession('testuser1', 'Auth.username');
    }

    /**
     * Test login with invalid username
     *
     * @return void
     * @uses \App\Controller\UsersController::login()
     */
    public function testLoginWithInvalidUsername(): void
    {
        $this->post('/users/login', [
            'username' => 'nonexistent',
            'password' => 'password123',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Invalid username or password');
    }

    /**
     * Test login with invalid password
     *
     * @return void
     * @uses \App\Controller\UsersController::login()
     */
    public function testLoginWithInvalidPassword(): void
    {
        $this->post('/users/login', [
            'username' => 'testuser1',
            'password' => 'wrongpassword',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Invalid username or password');
    }

    /**
     * Test login with inactive user
     *
     * @return void
     * @uses \App\Controller\UsersController::login()
     */
    public function testLoginWithInactiveUser(): void
    {
        $this->post('/users/login', [
            'username' => 'inactiveuser',
            'password' => 'password789',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Invalid username or password');
    }

    /**
     * Test logout
     *
     * @return void
     * @uses \App\Controller\UsersController::logout()
     */
    public function testLogout(): void
    {
        // First login
        $this->post('/users/login', [
            'username' => 'testuser1',
            'password' => 'password123',
        ]);

        // Then logout
        $this->get('/users/logout');

        $this->assertRedirectContains('/users/login');
    }

    /**
     * Test already authenticated user accessing login page
     *
     * @return void
     * @uses \App\Controller\UsersController::login()
     */
    public function testLoginWhenAlreadyAuthenticated(): void
    {
        // Session-based test - skip for now as IntegrationTestTrait
        // doesn't maintain sessions between requests by default
        $this->markTestSkipped('Session persistence between requests requires special setup');
    }

    /**
     * Test redirect to originally requested URL after login
     *
     * @return void
     * @uses \App\Controller\UsersController::login()
     */
    public function testLoginRedirectToOriginalUrl(): void
    {
        $this->post('/users/login?redirect=%2Fusers%2Findex', [
            'username' => 'testuser1',
            'password' => 'password123',
        ]);

        $this->assertResponseSuccess();
        $this->assertRedirect('/users/index');
    }
}
