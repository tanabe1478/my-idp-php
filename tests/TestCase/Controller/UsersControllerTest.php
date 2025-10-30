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
        'app.SocialAccounts',
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

    /**
     * Test socialLogin with Google redirects to Google OAuth
     *
     * @return void
     * @uses \App\Controller\UsersController::socialLogin()
     */
    public function testSocialLoginWithGoogleRedirectsToGoogleOAuth(): void
    {
        $this->get('/users/login/google');

        $this->assertResponseCode(302);
        $this->assertRedirectContains('accounts.google.com');
    }

    /**
     * Test socialLogin with GitHub redirects to GitHub OAuth
     *
     * @return void
     * @uses \App\Controller\UsersController::socialLogin()
     */
    public function testSocialLoginWithGitHubRedirectsToGitHubOAuth(): void
    {
        $this->get('/users/login/github');

        $this->assertResponseCode(302);
        $this->assertRedirectContains('github.com');
    }

    /**
     * Test socialLogin with unsupported provider returns error
     *
     * @return void
     * @uses \App\Controller\UsersController::socialLogin()
     */
    public function testSocialLoginWithUnsupportedProviderReturnsError(): void
    {
        $this->get('/users/login/invalid');

        $this->assertResponseCode(500);
    }

    /**
     * Test socialCallback creates new user and logs in
     *
     * @return void
     * @uses \App\Controller\UsersController::socialCallback()
     */
    public function testSocialCallbackCreatesNewUserAndLogsIn(): void
    {
        // This test will be skipped as it requires mocking external OAuth API calls
        $this->markTestSkipped('Requires mocking external OAuth provider API calls');
    }

    /**
     * Test socialCallback logs in existing user
     *
     * @return void
     * @uses \App\Controller\UsersController::socialCallback()
     */
    public function testSocialCallbackLogsInExistingUser(): void
    {
        // This test will be skipped as it requires mocking external OAuth API calls
        $this->markTestSkipped('Requires mocking external OAuth provider API calls');
    }

    /**
     * Test socialCallback with missing code parameter
     *
     * @return void
     * @uses \App\Controller\UsersController::socialCallback()
     */
    public function testSocialCallbackWithMissingCodeParameter(): void
    {
        $this->get('/users/callback/google');

        $this->assertResponseCode(302);
        $this->assertRedirectContains('/users/login');
        $this->assertFlashMessage('Authentication failed', 'flash');
    }

    /**
     * Test socialCallback with error parameter
     *
     * @return void
     * @uses \App\Controller\UsersController::socialCallback()
     */
    public function testSocialCallbackWithErrorParameter(): void
    {
        $this->get('/users/callback/google?error=access_denied');

        $this->assertResponseCode(302);
        $this->assertRedirectContains('/users/login');
        $this->assertFlashMessage('Authentication cancelled', 'flash');
    }
}
