<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Social\SocialAuthService;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * Users Controller
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    /**
     * Before filter callback
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // Allow public access to login and social auth actions
        $this->Authentication->addUnauthenticatedActions(['login', 'socialLogin', 'socialCallback']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // Get current user
        $currentUser = $this->Authentication->getIdentity();

        // Load user's social accounts
        $user = $this->Users->get($currentUser->id, [
            'contain' => ['SocialAccounts']
        ]);

        $this->set('currentUser', $user);
        $this->set('users', $this->paginate($this->Users));
    }

    /**
     * Login method
     *
     * @return \Cake\Http\Response|null Redirects on successful login
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();

        // If POST request, process authentication
        if ($this->request->is('post')) {
            if ($result && $result->isValid()) {
                // Check if user is active
                $user = $this->Authentication->getIdentity();
                if ($user && !$user->is_active) {
                    $this->Authentication->logout();
                    $this->Flash->error('Invalid username or password');
                } else {
                    // Successful login
                    $target = $this->Authentication->getLoginRedirect() ?? ['action' => 'index'];

                    return $this->redirect($target);
                }
            } else {
                $this->Flash->error('Invalid username or password');
            }
        } else {
            // GET request - if already authenticated, redirect to index
            if ($result && $result->isValid()) {
                return $this->redirect(['action' => 'index']);
            }
        }
    }

    /**
     * Logout method
     *
     * @return \Cake\Http\Response|null Redirects to login
     */
    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
        }

        return $this->redirect(['action' => 'login']);
    }

    /**
     * Social login - redirects to OAuth provider
     *
     * @param string $provider Provider name (google, github)
     * @return \Cake\Http\Response Redirects to OAuth provider
     * @throws \InvalidArgumentException
     */
    public function socialLogin(string $provider)
    {
        $this->request->allowMethod(['get']);

        // Get social auth configuration
        $config = Configure::read('SocialAuth');
        $socialAuthService = new SocialAuthService($config);

        // Build callback URL - must be absolute URL
        $redirectUri = $this->request->getUri()
            ->withPath('/users/callback/' . $provider)
            ->withQuery('')
            ->withFragment('');

        // Store redirect URI in session for later verification
        $this->request->getSession()->write('oauth_redirect_uri', (string)$redirectUri);

        // Debug log
        \Cake\Log\Log::debug("OAuth login - Provider: {$provider}, Redirect URI: {$redirectUri}");

        // Get authorization URL with state parameter for CSRF protection
        $state = bin2hex(random_bytes(16));
        $this->request->getSession()->write('oauth_state', $state);

        $authUrl = $socialAuthService->getAuthorizationUrl(
            $provider,
            (string)$redirectUri,
            ['state' => $state]
        );

        return $this->redirect($authUrl);
    }

    /**
     * Social callback - handles OAuth callback
     *
     * @param string $provider Provider name (google, github)
     * @return \Cake\Http\Response|null Redirects after authentication
     */
    public function socialCallback(string $provider)
    {
        $this->request->allowMethod(['get']);

        // Check for error response
        $error = $this->request->getQuery('error');
        if ($error) {
            $this->Flash->error('Authentication cancelled');

            return $this->redirect(['action' => 'login']);
        }

        // Validate code parameter
        $code = $this->request->getQuery('code');
        if (!$code) {
            $this->Flash->error('Authentication failed');

            return $this->redirect(['action' => 'login']);
        }

        // Debug log
        \Cake\Log\Log::debug("Received authorization code: " . substr($code, 0, 15) . "... (length: " . strlen($code) . ")");

        // Verify state parameter (CSRF protection)
        $state = $this->request->getQuery('state');
        $sessionState = $this->request->getSession()->read('oauth_state');
        $this->request->getSession()->delete('oauth_state');

        if (!$state || $state !== $sessionState) {
            $this->Flash->error('Invalid state parameter');

            return $this->redirect(['action' => 'login']);
        }

        try {
            // Get social auth configuration
            $config = Configure::read('SocialAuth');
            $socialAuthService = new SocialAuthService($config);

            // Get the same redirect URI that was used in authorization request
            $redirectUri = $this->request->getSession()->read('oauth_redirect_uri');
            $this->request->getSession()->delete('oauth_redirect_uri');

            if (!$redirectUri) {
                throw new \RuntimeException('Missing redirect URI in session');
            }

            // Debug log
            \Cake\Log\Log::debug("OAuth callback - Provider: {$provider}, Redirect URI: {$redirectUri}");

            // Check if user is already logged in (account linking)
            $currentUser = $this->Authentication->getIdentity();

            if ($currentUser) {
                // User is already logged in - link social account
                $user = $socialAuthService->linkAccount(
                    $provider,
                    $code,
                    $redirectUri,
                    $currentUser->getIdentifier()
                );

                $this->Flash->success('Social account connected successfully!');

                return $this->redirect(['action' => 'index']);
            }

            // Not logged in - authenticate user
            $user = $socialAuthService->authenticate(
                $provider,
                $code,
                $redirectUri
            );

            // Check if user is active
            if (!$user->is_active) {
                $this->Flash->error('Your account is not active');

                return $this->redirect(['action' => 'login']);
            }

            // Set user identity
            $this->Authentication->setIdentity($user);

            $this->Flash->success('Welcome back!');

            return $this->redirect(['action' => 'index']);
        } catch (\Exception $e) {
            // Detailed error logging
            \Cake\Log\Log::error(sprintf(
                'OAuth authentication failed - Provider: %s, Error: %s, Trace: %s',
                $provider,
                $e->getMessage(),
                $e->getTraceAsString()
            ));

            $this->Flash->error('Authentication failed: ' . $e->getMessage());

            return $this->redirect(['action' => 'login']);
        }
    }
}
