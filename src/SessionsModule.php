<?php

namespace Cencori;

/**
 * Sessions module for managing durable execution sessions for AI agents.
 *
 * Sessions provide pause/resume AI agent execution with event sourcing,
 * human-in-the-loop approval workflows, and turn-based interaction.
 *
 * @example
 * // Create a session
 * $session = $cencori->sessions->create(['agent_id' => 'ag_...']);
 *
 * // Submit a turn
 * $cencori->sessions->submitTurn($session['id'], ['input' => 'Hello!']);
 *
 * // List active sessions
 * $sessions = $cencori->sessions->list(['status' => 'active']);
 */
class SessionsModule
{
    private Cencori $client;

    public function __construct(Cencori $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new session.
     *
     * @param array $params Session creation parameters:
     *   - agent_id: ?string
     *   - metadata: ?array
     * @return array Session object
     */
    public function create(array $params = []): array
    {
        return $this->client->request('/v1/sessions', 'POST', $params);
    }

    /**
     * List sessions with optional filtering.
     *
     * @param array $params Filter parameters:
     *   - page: ?int
     *   - limit: ?int
     *   - status: ?string ('active', 'paused', 'completed', 'failed')
     *   - agent_id: ?string
     * @return array{data: array, pagination: array} Paginated session list
     */
    public function list(array $params = []): array
    {
        $query = [];

        if (isset($params['page'])) {
            $query[] = 'page=' . $params['page'];
        }
        if (isset($params['limit'])) {
            $query[] = 'limit=' . $params['limit'];
        }
        if (isset($params['status'])) {
            $query[] = 'status=' . urlencode($params['status']);
        }
        if (isset($params['agent_id'])) {
            $query[] = 'agent_id=' . urlencode($params['agent_id']);
        }

        $path = '/v1/sessions';
        if (!empty($query)) {
            $path .= '?' . implode('&', $query);
        }

        return $this->client->request($path, 'GET');
    }

    /**
     * Get a session by ID.
     *
     * @param string $sessionId The session ID
     * @return array Session object
     */
    public function get(string $sessionId): array
    {
        return $this->client->request("/v1/sessions/{$sessionId}", 'GET');
    }

    /**
     * Delete a session by ID.
     *
     * @param string $sessionId The session ID
     * @return array{id: string, deleted: bool}
     */
    public function delete(string $sessionId): array
    {
        return $this->client->request("/v1/sessions/{$sessionId}", 'DELETE');
    }

    /**
     * Submit a turn in a session.
     *
     * For streaming, pass 'stream' => true in params and handle
     * the raw response via buildRequestOptions().
     *
     * @param string $sessionId The session ID
     * @param array $params Turn parameters:
     *   - input: string|array (required) - The turn input
     *   - tools: ?array
     *   - instructions: ?string
     *   - agent_id: ?string
     *   - model: ?string
     *   - temperature: ?float
     *   - max_output_tokens: ?int
     *   - tool_choice: ?string|array
     *   - response_format: ?array
     *   - user: ?string
     *   - pause_on_tool_calls: ?bool
     * @return array Turn response data
     */
    public function submitTurn(string $sessionId, array $params): array
    {
        $build = $this->client->buildRequestOptions('POST', $params);
        $url = $this->client->getBaseUrl() . "/v1/sessions/{$sessionId}/turns";

        $response = $this->client->getHttpClient()->request('POST', $url, $build['options']);

        $body = (string) $response->getBody();
        return json_decode($body, true) ?? [];
    }

    /**
     * Get events for a session.
     *
     * @param string $sessionId The session ID
     * @param array $params Event filter parameters:
     *   - page: ?int
     *   - limit: ?int
     *   - turn_number: ?int
     * @return array{data: array, pagination: array} Paginated events list
     */
    public function getEvents(string $sessionId, array $params = []): array
    {
        $query = [];

        if (isset($params['page'])) {
            $query[] = 'page=' . $params['page'];
        }
        if (isset($params['limit'])) {
            $query[] = 'limit=' . $params['limit'];
        }
        if (isset($params['turn_number'])) {
            $query[] = 'turn_number=' . $params['turn_number'];
        }

        $path = "/v1/sessions/{$sessionId}/events";
        if (!empty($query)) {
            $path .= '?' . implode('&', $query);
        }

        return $this->client->request($path, 'GET');
    }

    /**
     * Approve a pending action in a session.
     *
     * @param string $sessionId The session ID
     * @param array $params Approval parameters:
     *   - action_id: string (required)
     *   - tool_results: ?array
     * @return array Response data
     */
    public function approve(string $sessionId, array $params): array
    {
        $build = $this->client->buildRequestOptions('POST', $params);
        $url = $this->client->getBaseUrl() . "/v1/sessions/{$sessionId}/approve";

        $response = $this->client->getHttpClient()->request('POST', $url, $build['options']);

        $body = (string) $response->getBody();
        return json_decode($body, true) ?? [];
    }

    /**
     * Reject a pending action in a session.
     *
     * @param string $sessionId The session ID
     * @param array $params Rejection parameters:
     *   - action_id: string (required)
     *   - tool_results: ?array
     * @return array{id: string, action_id: string, resolution: string, status: string}
     */
    public function reject(string $sessionId, array $params): array
    {
        return $this->client->request("/v1/sessions/{$sessionId}/reject", 'POST', $params);
    }
}
