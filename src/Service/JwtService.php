<?php
declare(strict_types=1);

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * JWT Service
 *
 * Handles JWT token generation and verification
 */
class JwtService
{
    /**
     * JWT signing algorithm
     */
    private const ALGORITHM = 'HS256';

    /**
     * JWT secret key
     *
     * @var string
     */
    protected string $secret;

    /**
     * JWT issuer
     *
     * @var string
     */
    protected string $issuer;

    /**
     * Constructor
     *
     * @param string|null $secret JWT secret key (from config)
     * @param string|null $issuer JWT issuer (from config)
     */
    public function __construct(?string $secret = null, ?string $issuer = null)
    {
        // Use provided secret, or fall back to configuration
        if ($secret === null) {
            // Try to get from environment variable first (most secure)
            $envSecret = env('JWT_SECRET_KEY');
            if ($envSecret) {
                $this->secret = $envSecret;
            } else {
                // Fall back to Security.salt for development/testing
                $configSecret = \Cake\Core\Configure::read('Security.salt');
                // If no config, generate a random secret (last resort for testing)
                $this->secret = $configSecret ?: $this->generateSecret();
            }
        } else {
            $this->secret = $secret;
        }
        $this->issuer = $issuer ?? env('JWT_ISSUER', 'http://localhost:8765');
    }

    /**
     * Generate an access token
     *
     * @param string $clientId Client identifier
     * @param string $userId User identifier
     * @param array $scopes Array of granted scopes
     * @param int $expiresIn Token lifetime in seconds (default: 3600 = 1 hour)
     * @return string JWT access token
     */
    public function generateAccessToken(
        string $clientId,
        string $userId,
        array $scopes,
        int $expiresIn = 3600
    ): string {
        $issuedAt = time();
        $expires = $issuedAt + $expiresIn;

        $payload = [
            'iss' => $this->issuer,           // Issuer
            'sub' => $userId,                 // Subject (user ID)
            'aud' => $clientId,               // Audience (client ID)
            'iat' => $issuedAt,               // Issued at
            'exp' => $expires,                // Expiration
            'scope' => implode(' ', $scopes), // Scopes
        ];

        return JWT::encode($payload, $this->secret, self::ALGORITHM);
    }

    /**
     * Generate an ID token (OpenID Connect)
     *
     * @param string $clientId Client identifier
     * @param string $userId User identifier
     * @param string $username User's username
     * @param string $email User's email
     * @param array $scopes Array of granted scopes
     * @param int $expiresIn Token lifetime in seconds (default: 3600 = 1 hour)
     * @return string JWT ID token
     */
    public function generateIdToken(
        string $clientId,
        string $userId,
        string $username,
        string $email,
        array $scopes,
        int $expiresIn = 3600
    ): string {
        $issuedAt = time();
        $expires = $issuedAt + $expiresIn;

        $payload = [
            'iss' => $this->issuer,    // Issuer
            'sub' => $userId,          // Subject (user ID)
            'aud' => $clientId,        // Audience (client ID)
            'iat' => $issuedAt,        // Issued at
            'exp' => $expires,         // Expiration
            'auth_time' => $issuedAt,  // Authentication time
        ];

        // Add optional claims based on scopes
        if (in_array('profile', $scopes)) {
            $payload['preferred_username'] = $username;
        }

        if (in_array('email', $scopes)) {
            $payload['email'] = $email;
            $payload['email_verified'] = true;
        }

        return JWT::encode($payload, $this->secret, self::ALGORITHM);
    }

    /**
     * Verify and decode a JWT token
     *
     * @param string $token JWT token to verify
     * @return object Decoded token payload
     * @throws \Exception If token is invalid
     */
    public function verifyToken(string $token): object
    {
        return JWT::decode($token, new Key($this->secret, self::ALGORITHM));
    }

    /**
     * Generate a random secret key
     *
     * @return string
     */
    protected function generateSecret(): string
    {
        return bin2hex(random_bytes(32)); // 64 character hex string
    }

    /**
     * Get the current issuer
     *
     * @return string
     */
    public function getIssuer(): string
    {
        return $this->issuer;
    }
}
