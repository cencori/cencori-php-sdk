<?php

namespace Cencori;

use Cencori\Types\{APIKey, CreateAPIKeyParams, KeyUsageStats};

/**
 * Module for managing Cencori API keys.
 */
class APIKeysModule
{
    private Cencori $client;

    public function __construct(Cencori $client)
    {
        $this->client = $client;
    }

    /**
     * List API keys for a project.
     *
     * @param string $projectId The project ID
     * @param string $environment Environment name (e.g., "production", "test")
     * @return APIKey[]
     */
    public function list(string $projectId, string $environment): array
    {
        $path = "/api/projects/{$projectId}/api-keys?environment={$environment}";
        $data = $this->client->request($path, 'GET');

        $keys = [];
        foreach ($data['keys'] ?? [] as $k) {
            $keys[] = APIKey::fromArray($k);
        }
        return $keys;
    }

    /**
     * Create a new API key.
     *
     * @param string $projectId The project ID
     * @param CreateAPIKeyParams $params Creation parameters
     * @return APIKey Created API key (includes the secret key string)
     */
    public function create(string $projectId, CreateAPIKeyParams $params): APIKey
    {
        $path = "/api/projects/{$projectId}/api-keys";
        $data = $this->client->request($path, 'POST', $params->toArray());
        return APIKey::fromArray($data);
    }

    /**
     * Revoke (delete) an API key.
     *
     * @param string $projectId The project ID
     * @param string $keyId The API key ID
     */
    public function revoke(string $projectId, string $keyId): void
    {
        $path = "/api/projects/{$projectId}/api-keys/{$keyId}";
        $this->client->request($path, 'DELETE');
    }

    /**
     * Get usage statistics for an API key.
     *
     * @param string $projectId The project ID
     * @param string $keyId The API key ID
     * @return KeyUsageStats
     */
    public function getStats(string $projectId, string $keyId): KeyUsageStats
    {
        $path = "/api/projects/{$projectId}/api-keys/{$keyId}/stats";
        $data = $this->client->request($path, 'GET');
        return KeyUsageStats::fromArray($data);
    }
}
