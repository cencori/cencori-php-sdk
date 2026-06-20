<?php

namespace Cencori;

/**
 * Agents module for creating and managing AI agents.
 *
 * @example
 * $agent = $cencori->agents->create([
 *     'name' => 'my-agent',
 *     'config' => ['model' => 'gpt-4o', 'system_prompt' => 'You are a helpful assistant.']
 * ]);
 * $key = $cencori->agents->createKey($agent['id'], ['name' => 'prod-key']);
 */
class AgentsModule
{
    private Cencori $client;

    public function __construct(Cencori $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new agent.
     *
     * @param array $params Agent creation parameters:
     *   - name: string (required)
     *   - description: ?string
     *   - config: ?array{model?: string, system_prompt?: string, tools?: string[], temperature?: float}
     * @return array Agent object
     */
    public function create(array $params): array
    {
        return $this->client->request('/v1/agents', 'POST', $params);
    }

    /**
     * List all agents.
     *
     * @return array{data: array} Array of agent list items
     */
    public function list(): array
    {
        return $this->client->request('/v1/agents', 'GET');
    }

    /**
     * Get a specific agent by ID.
     *
     * @param string $agentId The agent ID
     * @return array Agent object
     */
    public function get(string $agentId): array
    {
        return $this->client->request("/v1/agents/{$agentId}", 'GET');
    }

    /**
     * Update an agent's configuration.
     *
     * @param string $agentId The agent ID
     * @param array $params Update parameters (partial agent config)
     * @return array Updated agent object
     */
    public function updateConfig(string $agentId, array $params): array
    {
        return $this->client->request("/v1/agents/{$agentId}", 'PATCH', $params);
    }

    /**
     * Delete an agent.
     *
     * @param string $agentId The agent ID
     * @return void
     */
    public function delete(string $agentId): void
    {
        $this->client->request("/v1/agents/{$agentId}", 'DELETE');
    }

    /**
     * Create an API key for an agent.
     *
     * @param string $agentId The agent ID
     * @param array $params Key creation parameters:
     *   - name: ?string
     *   - environment: ?string ('production', 'test')
     *   - key_type: ?string ('secret', 'publishable')
     *   - allowed_domains: ?string[]
     * @return array Agent key object
     */
    public function createKey(string $agentId, array $params = []): array
    {
        return $this->client->request("/v1/agents/{$agentId}/keys", 'POST', $params);
    }
}
