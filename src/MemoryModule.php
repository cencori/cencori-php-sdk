<?php

namespace Cencori;

use Cencori\Errors\CencoriError;

/**
 * Memory module for vector storage, RAG, conversation history, and semantic search.
 *
 * @example
 * // Create a namespace
 * $namespace = $cencori->memory->createNamespace(['name' => 'conversations']);
 *
 * // Store a memory
 * $memory = $cencori->memory->store([
 *     'namespace' => 'conversations',
 *     'content' => 'User asked about pricing plans',
 *     'metadata' => ['userId' => 'user_123'],
 * ]);
 *
 * // Search memories
 * $results = $cencori->memory->search([
 *     'namespace' => 'conversations',
 *     'query' => 'what did we discuss about pricing?',
 *     'limit' => 5,
 * ]);
 */
class MemoryModule
{
    private Cencori $client;

    public function __construct(Cencori $client)
    {
        $this->client = $client;
    }

    private function request(string $method, string $endpoint, ?array $body = null): array
    {
        return $this->client->request($endpoint, $method, $body);
    }

    // ==================
    // Namespace Methods
    // ==================

    /**
     * Create a new memory namespace.
     *
     * @param array $options Namespace options:
     *   - name: string (required)
     *   - description: ?string
     *   - embeddingModel: ?string
     *   - dimensions: ?int
     *   - metadata: ?array
     * @return array{id: string, name: string, description?: string, embeddingModel: string, dimensions: int, metadata: array, memoryCount?: int, createdAt: string}
     */
    public function createNamespace(array $options): array
    {
        return $this->request('POST', '/api/memory/namespaces', $options);
    }

    /**
     * List all namespaces.
     *
     * @return array Array of namespace objects
     */
    public function listNamespaces(): array
    {
        $response = $this->request('GET', '/api/memory/namespaces');
        return $response['namespaces'] ?? [];
    }

    // ==================
    // Memory Methods
    // ==================

    /**
     * Store a memory in a namespace.
     *
     * @param array $options Store options:
     *   - namespace: string (required)
     *   - content: string (required)
     *   - embedding: ?float[]
     *   - metadata: ?array
     *   - expiresAt: ?string
     * @return array{id: string, namespace: string, content: string, metadata: array, expiresAt?: string, createdAt: string}
     */
    public function store(array $options): array
    {
        $body = $options;

        // Convert DateTime to ISO string
        if (isset($body['expiresAt']) && $body['expiresAt'] instanceof \DateTimeInterface) {
            $body['expiresAt'] = $body['expiresAt']->format(\DateTimeInterface::ATOM);
        }

        return $this->request('POST', '/api/memory/store', $body);
    }

    /**
     * Semantic search across memories.
     *
     * @param array $options Search options:
     *   - namespace: string (required)
     *   - query: string (required)
     *   - limit: ?int
     *   - threshold: ?float
     *   - filter: ?array
     * @return array{results: array, query: string, namespace: string, count: int, latencyMs: int}
     */
    public function search(array $options): array
    {
        return $this->request('POST', '/api/memory/search', $options);
    }

    /**
     * Get a memory by ID.
     *
     * @param string $id The memory ID
     * @return array{id: string, namespace: string, content: string, metadata: array, similarity?: float, expiresAt?: string, createdAt: string, updatedAt?: string}
     */
    public function get(string $id): array
    {
        return $this->request('GET', "/api/memory/{$id}");
    }

    /**
     * Delete a memory by ID.
     *
     * @param string $id The memory ID
     * @return array{deleted: bool, id: string}
     */
    public function delete(string $id): array
    {
        return $this->request('DELETE', "/api/memory/{$id}");
    }

    /**
     * Store multiple memories in batch.
     *
     * @param string $namespace The namespace to store in
     * @param array $items Array of items with 'content' and optional 'metadata' keys
     * @return array Array of stored memory objects
     */
    public function storeBatch(string $namespace, array $items): array
    {
        $results = [];
        foreach ($items as $item) {
            $results[] = $this->store(array_merge(
                ['namespace' => $namespace],
                $item
            ));
        }
        return $results;
    }

    /**
     * Delete all memories in a namespace matching a filter.
     *
     * @param string $namespace The namespace
     * @param array $filter Key-value pairs to filter by
     * @return array{deleted: int}
     */
    public function deleteByFilter(string $namespace, array $filter): array
    {
        // First search to find matching memories
        $searchResult = $this->search([
            'namespace' => $namespace,
            'query' => '*',
            'limit' => 1000,
            'threshold' => 0,
            'filter' => $filter,
        ]);

        // Delete each one
        $deleted = 0;
        foreach ($searchResult['results'] ?? [] as $memory) {
            $this->delete($memory['id']);
            $deleted++;
        }

        return ['deleted' => $deleted];
    }
}
