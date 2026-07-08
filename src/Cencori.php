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
 * Cencori SDK client v1.4.0
 *
 * One SDK for AI Gateway, Agents, Memory, Compute, Workflow, and Storage.
 * Every operation is secured, logged, and tracked.
 *
 * @example
 * $cencori = new Cencori(['apiKey' => 'csk_...']);
 * $response = $cencori->ai->chat(
 *     messages: [['role' => 'user', 'content' => 'Hello!']],
 *     model: 'gpt-4o',
 * );
 */
class Cencori
{
    private string $apiKey;
    private string $baseUrl;
    private float $timeout;
    private array $headers;
    private ClientInterface $httpClient;
    private int $maxRetries;

    /** AI module for chat, completions, embeddings, RAG, image gen, structured output. */
    public AIModule $ai;

    /** Vision module for analyze / describe / OCR / classify on images. */
    public VisionModule $vision;

    /** Documents module for extract / summarize / query on PDFs and images. */
    public DocumentsModule $documents;

    /** Agents module for creating and managing AI agents. */
    public AgentsModule $agents;

    /** Memory module for vector storage, RAG, semantic search. */
    public MemoryModule $memory;

    /** Telemetry module for reporting web traffic. */
    public TelemetryModule $telemetry;

    /** Sessions module for durable execution and pause/resume workflows. */
    public SessionsModule $sessions;

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
     * @param array $config Configuration options:
     *   - apiKey: string (falls back to CENCORI_API_KEY env var)
     *   - baseUrl: string (default: https://api.cencori.com)
     *   - headers: array (custom headers to include in requests)
     *   - timeout: float (request timeout in seconds, default: 30.0)
     *   - maxRetries: int (max retries on 5xx, default: 3)
     * @throws \InvalidArgumentException if no API key is provided
     *
     * @example
     * $cencori = new Cencori(['apiKey' => 'csk_...']);
     * $cencori = new Cencori(['apiKey' => 'csk_...', 'baseUrl' => 'https://api.cencori.com', 'headers' => ['X-Custom' => 'value']]);
     */
    public function __construct(
        array $config = [],
    ) {
        $apiKey = $config['apiKey'] ?? getenv('CENCORI_API_KEY');

        if ($apiKey === false || $apiKey === '') {
            throw new \InvalidArgumentException(
                'Cencori API key is required. Pass it via new Cencori([\'apiKey\' => \'csk_...\']) '
                . 'or set the CENCORI_API_KEY environment variable.'
            );
        }

        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($config['baseUrl'] ?? 'https://api.cencori.com', '/');
        $this->headers = $config['headers'] ?? [];
        $this->timeout = $config['timeout'] ?? 30.0;
        $this->maxRetries = $config['maxRetries'] ?? 3;
        $this->httpClient = $config['httpClient'] ?? new Client(['timeout' => $this->timeout]);

        $this->ai = new AIModule($this);
        $this->vision = new VisionModule($this);
        $this->documents = new DocumentsModule($this);
        $this->agents = new AgentsModule($this);
        $this->memory = new MemoryModule($this);
        $this->telemetry = new TelemetryModule($this);
        $this->sessions = new SessionsModule($this);
        $this->projects = new ProjectsModule($this);
        $this->apiKeys = new APIKeysModule($this);
        $this->metrics = new MetricsModule($this);
        $this->compute = new ComputeModule();
        $this->workflow = new WorkflowModule();
        $this->storage = new StorageModule();
    }

    /**
     * Make a generic HTTP request to the Cencori API with retry support.
     *
     * @param string $endpoint API endpoint path (e.g., "/api/ai/chat")
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
            $this->headers,
            $headers ?? [],
        );

        $options = [
            'headers' => $requestHeaders,
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        $lastException = null;

        for ($attempt = 0; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = $this->httpClient->request($method, $url, $options);

                // Return immediately on success or on 4xx (client errors)
                if ($response->getStatusCode() < 500) {
                    return $this->handleResponse($response);
                }

                // On 5xx, retry with exponential backoff
                $lastException = new CencoriError(
                    message: "Server error: HTTP {$response->getStatusCode()}",
                    statusCode: $response->getStatusCode(),
                );

                if ($attempt === $this->maxRetries) {
                    throw $lastException;
                }

                // Exponential backoff: 1s, 2s, 4s
                $sleepMs = (int) (pow(2, $attempt) * 1000000);
                usleep($sleepMs);
            } catch (CencoriError $e) {
                throw $e;
            } catch (\Throwable $e) {
                $lastException = $e;

                if ($attempt === $this->maxRetries) {
                    break;
                }

                // Exponential backoff on connection errors too
                $sleepMs = (int) (pow(2, $attempt) * 1000000);
                usleep($sleepMs);
            }
        }

        throw new CencoriError(
            message: 'Request failed after ' . ($this->maxRetries + 1) . ' attempts: ' . ($lastException?->getMessage() ?? 'unknown error'),
        );
    }

    /**
     * Raw HTTP request helper for streaming or non-JSON responses.
     * Does not apply retry logic — the caller controls that.
     *
     * @return array{headers: array, options: array}
     */
    public function buildRequestOptions(
        string $method = 'POST',
        ?array $body = null,
        ?array $extraHeaders = null,
    ): array {
        $headers = array_merge(
            [
                'Content-Type' => 'application/json',
                'CENCORI_API_KEY' => $this->apiKey,
            ],
            $this->headers,
            $extraHeaders ?? [],
        );

        $options = [
            'headers' => $headers,
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        return ['headers' => $headers, 'options' => $options];
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

    public function getHeaders(): array
    {
        return $this->headers;
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
