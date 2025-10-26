<?php
declare(strict_types=1);

namespace App\Controller;

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

        // Allow public access to login action
        $this->Authentication->addUnauthenticatedActions(['login']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
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
}
