<?php

namespace Cencori;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

use Cencori\Errors\{
    AuthenticationError,
    CencoriError,
    RateLimitError,
    SafetyError,
};

/**
 * Cencori SDK client.
 *
 * One SDK for AI Gateway, Compute, Workflow, and Storage.
 * Every operation is secured, logged, and tracked.
 */
class Cencori
{
    private string $apiKey;
    private string $baseUrl;
    private float $timeout;
    private ClientInterface $httpClient;

    /** AI module for chat completions, embeddings, and streaming. */
    public AIModule $ai;

    /** Projects module for managing Cencori projects. */
    public ProjectsModule $projects;

    /** API Keys module for managing API keys. */
    public APIKeysModule $apiKeys;

    /** Metrics module for fetching usage metrics. */
    public MetricsModule $metrics;

    /** Compute module - Serverless functions & GPU access. 🚧 Coming Soon */
    public ComputeModule $compute;

    /** Workflow module - AI pipelines & orchestration. 🚧 Coming Soon */
    public WorkflowModule $workflow;

    /** Storage module - Vector database, knowledge base, RAG. 🚧 Coming Soon */
    public StorageModule $storage;

    /**
     * Create a new Cencori client.
     *
     * @param string|null $apiKey API key (falls back to CENCORI_API_KEY env var)
     * @param string $baseUrl Base URL (default: https://cencori.com)
     * @param float $timeout Request timeout in seconds (default: 30.0)
     * @param ClientInterface|null $httpClient Optional HTTP client
     * @throws \InvalidArgumentException if no API key is provided
     */
    public function __construct(
        ?string $apiKey = null,
        string $baseUrl = 'https://cencori.com',
        float $timeout = 30.0,
        ?ClientInterface $httpClient = null,
    ) {
        $apiKey = $apiKey ?? getenv('CENCORI_API_KEY');

        if ($apiKey === false || $apiKey === '') {
            throw new \InvalidArgumentException(
                'Cencori API key is required. Pass it via Cencori(apiKey: "csk_...") '
                . 'or set the CENCORI_API_KEY environment variable.'
            );
        }

        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->httpClient = $httpClient ?? new Client(['timeout' => $timeout]);

        $this->ai = new AIModule($this);
        $this->projects = new ProjectsModule($this);
        $this->apiKeys = new APIKeysModule($this);
        $this->metrics = new MetricsModule($this);
        $this->compute = new ComputeModule();
        $this->workflow = new WorkflowModule();
        $this->storage = new StorageModule();
    }

    /**
     * Make a generic HTTP request to the Cencori API.
     *
     * @param string $endpoint API endpoint path (e.g., "/api/v1/custom")
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param array|null $body Request body as array
     * @param array|null $headers Additional headers
     * @return array Response data as array
     * @throws CencoriError on API errors
     */
    public function request(
        string $endpoint,
        string $method = 'GET',
        ?array $body = null,
        ?array $headers = null,
    ): array {
        $url = $this->baseUrl . $endpoint;

        $requestHeaders = array_merge(
            [
                'Content-Type' => 'application/json',
                'CENCORI_API_KEY' => $this->apiKey,
            ],
            $headers ?? [],
        );

        $options = [
            'headers' => $requestHeaders,
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        $response = $this->httpClient->request($method, $url, $options);
        return $this->handleResponse($response);
    }

    /**
     * Handle HTTP response and raise appropriate errors.
     *
     * @throws AuthenticationError
     * @throws RateLimitError
     * @throws SafetyError
     * @throws CencoriError
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 401) {
            throw new AuthenticationError();
        }

        if ($statusCode === 429) {
            throw new RateLimitError();
        }

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CencoriError(
                message: 'Invalid JSON response',
                statusCode: $statusCode,
            );
        }

        if ($statusCode === 400 && isset($data['reasons'])) {
            throw new SafetyError(
                message: $data['error'] ?? 'Content safety violation',
                reasons: $data['reasons'],
            );
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new CencoriError(
                message: $data['error'] ?? 'Request failed',
                statusCode: $statusCode,
            );
        }

        return $data;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Get configuration with masked API key hint.
     */
    public function getConfig(): array
    {
        $hint = strlen($this->apiKey) > 10
            ? substr($this->apiKey, 0, 6) . '...' . substr($this->apiKey, -4)
            : '...';

        return [
            'base_url' => $this->baseUrl,
            'api_key_hint' => $hint,
        ];
    }
}
