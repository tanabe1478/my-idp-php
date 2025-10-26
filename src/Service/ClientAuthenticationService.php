<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Client;
use App\Model\Table\ClientsTable;

/**
 * Client Authentication Service
 *
 * Handles OAuth2 client authentication
 */
class ClientAuthenticationService
{
    /**
     * Constructor
     *
     * @param \App\Model\Table\ClientsTable $clientsTable Clients table instance
     */
    public function __construct(
        protected ClientsTable $clientsTable
    ) {
    }

    /**
     * Authenticate a client using client_id and client_secret
     *
     * @param string $clientId Client ID
     * @param string|null $clientSecret Client secret (plain text)
     * @return \App\Model\Entity\Client|null Client entity if authenticated, null otherwise
     */
    public function authenticate(string $clientId, ?string $clientSecret): ?Client
    {
        // Find client by client_id
        $client = $this->clientsTable->findByClientId($clientId);

        if ($client === null) {
            return null;
        }

        // Check if client is active
        if (!$client->is_active) {
            return null;
        }

        // Handle authentication based on client type
        if ($client->is_confidential) {
            // Confidential clients must provide a valid secret
            return $this->authenticateConfidentialClient($client, $clientSecret);
        } else {
            // Public clients don't require secret verification
            return $this->authenticatePublicClient($client);
        }
    }

    /**
     * Authenticate a confidential client
     *
     * @param \App\Model\Entity\Client $client Client entity
     * @param string|null $clientSecret Plain text secret to verify
     * @return \App\Model\Entity\Client|null Client entity if authenticated, null otherwise
     */
    protected function authenticateConfidentialClient(Client $client, ?string $clientSecret): ?Client
    {
        // Confidential clients must provide a secret
        if ($clientSecret === null || $clientSecret === '') {
            return null;
        }

        // Verify the secret against the stored hash
        if ($client->client_secret === null) {
            return null;
        }

        if (!password_verify($clientSecret, $client->client_secret)) {
            return null;
        }

        return $client;
    }

    /**
     * Authenticate a public client
     *
     * Public clients don't have secrets and are authenticated by client_id alone
     *
     * @param \App\Model\Entity\Client $client Client entity
     * @return \App\Model\Entity\Client Client entity (always succeeds for active public clients)
     */
    protected function authenticatePublicClient(Client $client): Client
    {
        return $client;
    }
}
