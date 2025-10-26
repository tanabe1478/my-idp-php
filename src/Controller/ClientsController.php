<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Clients Controller
 *
 * @property \App\Model\Table\ClientsTable $Clients
 * @method \App\Model\Entity\Client[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ClientsController extends AppController
{
    /**
     * Ensure all actions require authentication
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        // All actions require authentication (no public actions)
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Clients->find()->contain(['Scopes']);
        $clients = $this->paginate($query);

        $this->set(compact('clients'));
    }

    /**
     * View method
     *
     * @param string|null $id Client id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $client = $this->Clients->get($id, contain: ['Scopes']);

        $this->set(compact('client'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $client = $this->Clients->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // Generate client_id and client_secret
            $data['client_id'] = $this->generateClientId();
            $data['client_secret'] = $this->generateClientSecret();

            $client = $this->Clients->patchEntity($client, $data);

            if ($this->Clients->save($client)) {
                $this->Flash->success(__('The client has been saved.'));

                return $this->redirect(['action' => 'view', $client->id]);
            }
            $this->Flash->error(__('The client could not be saved. Please, try again.'));
        }

        $scopes = $this->Clients->Scopes->find('list', limit: 200)->all();
        $this->set(compact('client', 'scopes'));
    }

    /**
     * Generate a unique client_id
     *
     * @return string
     */
    protected function generateClientId(): string
    {
        return bin2hex(random_bytes(16)); // 32 character hex string
    }

    /**
     * Generate a cryptographically secure client_secret
     *
     * @return string
     */
    protected function generateClientSecret(): string
    {
        return bin2hex(random_bytes(32)); // 64 character hex string
    }
}
