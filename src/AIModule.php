<?php

namespace Cencori;

use Cencori\Types\{
    ChatResponse,
    EmbeddingResponse,
    Message,
    StreamChunk,
};

/**
 * AI module for chat, completions, embeddings, RAG, image generation, and structured output.
 *
 * Provides access to OpenAI, Anthropic, and Google models through
 * Cencori's unified API with built-in security, logging, and cost tracking.
 */
class AIModule
{
    private Cencori $client;

    public function __construct(Cencori $client)
    {
        $this->client = $client;
    }

    /**
     * Send a chat completion request (non-streaming).
     *
     * @param array $messages List of message arrays with 'role' and 'content'
     * @param string $model AI model to use (default: gemini-2.5-flash)
     * @param float|null $temperature Sampling temperature (0-1)
     * @param int|null $maxTokens Maximum tokens in response
     * @param string|null $userId Optional user ID for rate limiting
     * @param array|null $tools Tool definitions for function calling
     * @param mixed $toolChoice How the model chooses to call tools ('auto', 'none', 'required', or array)
     * @return ChatResponse
     */
    public function chat(
        array $messages,
        string $model = 'gemini-2.5-flash',
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?string $userId = null,
        ?array $tools = null,
        mixed $toolChoice = null,
    ): ChatResponse {
        $payload = [
            'messages' => $messages,
            'model' => $model,
            'stream' => false,
        ];

        if ($temperature !== null) {
            $payload['temperature'] = $temperature;
        }
        if ($maxTokens !== null) {
            $payload['maxTokens'] = $maxTokens;
        }
        if ($userId !== null) {
            $payload['userId'] = $userId;
        }
        if ($tools !== null) {
            $payload['tools'] = $tools;
        }
        if ($toolChoice !== null) {
            $payload['toolChoice'] = $toolChoice;
        }

        $data = $this->client->request('/api/ai/chat', 'POST', $payload);
        return ChatResponse::fromArray($data);
    }

    /**
     * Send a chat completion request with streaming.
     *
     * Uses Guzzle's streaming response to parse SSE events.
     *
     * @param array $messages List of message arrays with 'role' and 'content'
     * @param string $model AI model to use (default: gemini-2.5-flash)
     * @param float|null $temperature Sampling temperature (0-1)
     * @param int|null $maxTokens Maximum tokens in response
     * @param string|null $userId Optional user ID for rate limiting
     * @param array|null $tools Tool definitions for function calling
     * @param mixed $toolChoice How the model chooses to call tools
     * @return \Generator|StreamChunk[]
     */
    public function chatStream(
        array $messages,
        string $model = 'gemini-2.5-flash',
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?string $userId = null,
        ?array $tools = null,
        mixed $toolChoice = null,
    ): \Generator {
        $payload = [
            'messages' => $messages,
            'model' => $model,
            'stream' => true,
        ];

        if ($temperature !== null) {
            $payload['temperature'] = $temperature;
        }
        if ($maxTokens !== null) {
            $payload['maxTokens'] = $maxTokens;
        }
        if ($userId !== null) {
            $payload['userId'] = $userId;
        }
        if ($tools !== null) {
            $payload['tools'] = $tools;
        }
        if ($toolChoice !== null) {
            $payload['toolChoice'] = $toolChoice;
        }

        $build = $this->client->buildRequestOptions('POST', $payload);
        $url = $this->client->getBaseUrl() . '/api/ai/chat';

        $response = $this->client->getHttpClient()->request('POST', $url, array_merge(
            $build['options'],
            ['stream' => true, 'timeout' => 60]
        ));

        $statusCode = $response->getStatusCode();
        if ($statusCode === 401) {
            throw new \Cencori\Errors\AuthenticationError();
        }
        if ($statusCode === 429) {
            throw new \Cencori\Errors\RateLimitError();
        }
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \Cencori\Errors\CencoriError(
                message: "Request failed with status $statusCode",
                statusCode: $statusCode,
            );
        }

        $body = $response->getBody();
        while (!$body->eof()) {
            $line = $this->readLine($body);
            if ($line === null) {
                break;
            }

            $line = trim($line);
            if (str_starts_with($line, 'data: ')) {
                $dataStr = substr($line, 6);

                if ($dataStr === '[DONE]') {
                    break;
                }

                $data = json_decode($dataStr, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }

                if (isset($data['error'])) {
                    yield StreamChunk::fromArray([
                        'delta' => '',
                        'error' => $data['error'],
                    ]);
                    return;
                }

                yield StreamChunk::fromArray([
                    'delta' => $data['delta'] ?? '',
                    'finish_reason' => $data['finish_reason'] ?? null,
                    'toolCalls' => $data['toolCalls'] ?? null,
                ]);
            }
        }
    }

    /**
     * Read a line from a stream, handling chunked encoding.
     */
    private function readLine($stream): ?string
    {
        $line = '';
        while (!$stream->eof()) {
            $char = $stream->read(1);
            if ($char === false || $char === '') {
                break;
            }
            if ($char === "\n") {
                break;
            }
            $line .= $char;
        }
        return $line === '' && $stream->eof() ? null : $line;
    }

    /**
     * Create a text completion (wraps chat internally).
     */
    public function completions(
        string $prompt,
        string $model = 'gemini-2.5-flash',
        ?float $temperature = null,
        ?int $maxTokens = null,
    ): ChatResponse {
        return $this->chat(
            messages: [Message::user($prompt)->toArray()],
            model: $model,
            temperature: $temperature,
            maxTokens: $maxTokens,
        );
    }

    /**
     * Generate embeddings for text.
     *
     * @param string|array $input Text or list of texts to embed
     * @param string $model Embedding model (default: text-embedding-3-small)
     * @return EmbeddingResponse
     */
    public function embeddings(
        string|array $input,
        string $model = 'text-embedding-3-small',
    ): EmbeddingResponse {
        $payload = [
            'input' => $input,
            'model' => $model,
        ];

        $data = $this->client->request('/api/ai/embeddings', 'POST', $payload);
        return EmbeddingResponse::fromArray($data, $model);
    }

    /**
     * Generate structured output with JSON schema.
     *
     * Uses function calling to enforce JSON schema output.
     *
     * @param string $model AI model to use
     * @param string $prompt Text prompt for the model
     * @param array $schema JSON Schema for the expected output
     * @param float|null $temperature Sampling temperature (0-1)
     * @param int|null $maxTokens Maximum tokens in response
     * @param string|null $schemaName Schema name for the model
     * @param string|null $schemaDescription Schema description
     * @return array{object: mixed, usage: array{promptTokens: int, completionTokens: int, totalTokens: int}}
     */
    public function generateObject(
        string $model,
        string $prompt,
        array $schema,
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?string $schemaName = null,
        ?string $schemaDescription = null,
    ): array {
        $schemaName = $schemaName ?? 'generate_object';
        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'maxTokens' => $maxTokens,
            'stream' => false,
            'tools' => [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => $schemaName,
                        'description' => $schemaDescription ?? 'Generate a structured object matching the schema',
                        'parameters' => $schema,
                    ],
                ],
            ],
            'toolChoice' => [
                'type' => 'function',
                'function' => ['name' => $schemaName],
            ],
        ];

        $data = $this->client->request('/api/ai/chat', 'POST', $payload);

        // Extract tool call from response
        $toolCall = $data['toolCalls'][0] ?? $data['tool_calls'][0] ?? $data['choices'][0]['message']['tool_calls'][0] ?? null;

        if ($toolCall === null) {
            throw new \Cencori\Errors\CencoriError('Model did not return structured output');
        }

        $parsedObject = json_decode($toolCall['function']['arguments'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Cencori\Errors\CencoriError('Failed to parse structured output as JSON');
        }

        $usage = $data['usage'] ?? [];

        return [
            'object' => $parsedObject,
            'usage' => [
                'promptTokens' => $usage['prompt_tokens'] ?? 0,
                'completionTokens' => $usage['completion_tokens'] ?? 0,
                'totalTokens' => $usage['total_tokens'] ?? 0,
            ],
        ];
    }

    /**
     * Generate images from a text prompt.
     *
     * @param string $prompt Text prompt describing the image
     * @param string $model Model to use (default: dall-e-3)
     * @param int|null $n Number of images to generate
     * @param string|null $size Image size (e.g., '1024x1024')
     * @param string|null $quality Image quality ('standard', 'hd')
     * @param string|null $style Image style ('vivid', 'natural')
     * @param string|null $responseFormat Response format ('url', 'b64_json')
     * @return array{images: array, model: string, provider: string}
     */
    public function generateImage(
        string $prompt,
        string $model = 'dall-e-3',
        ?int $n = null,
        ?string $size = null,
        ?string $quality = null,
        ?string $style = null,
        ?string $responseFormat = null,
    ): array {
        $payload = [
            'prompt' => $prompt,
            'model' => $model,
        ];

        if ($n !== null) {
            $payload['n'] = $n;
        }
        if ($size !== null) {
            $payload['size'] = $size;
        }
        if ($quality !== null) {
            $payload['quality'] = $quality;
        }
        if ($style !== null) {
            $payload['style'] = $style;
        }
        if ($responseFormat !== null) {
            $payload['responseFormat'] = $responseFormat;
        }

        $data = $this->client->request('/api/ai/images/generate', 'POST', $payload);

        $images = [];
        foreach ($data['images'] ?? [] as $img) {
            $images[] = [
                'url' => $img['url'] ?? null,
                'b64Json' => $img['b64_json'] ?? null,
                'revisedPrompt' => $img['revisedPrompt'] ?? null,
            ];
        }

        return [
            'images' => $images,
            'model' => $data['model'] ?? $model,
            'provider' => $data['provider'] ?? '',
        ];
    }

    /**
     * RAG (Retrieval-Augmented Generation) - Chat with automatic memory context.
     *
     * Searches memory namespace for relevant context and includes it
     * in the prompt automatically.
     *
     * @param string $model AI model to use
     * @param array $messages List of message arrays
     * @param string $namespace Memory namespace to search
     * @param float|null $temperature Sampling temperature (0-1)
     * @param int|null $maxTokens Maximum tokens in response
     * @param int|null $limit Number of memories to retrieve (default: 5)
     * @param float|null $threshold Similarity threshold (default: 0.5)
     * @param bool|null $includeSources Whether to include source documents (default: true)
     * @return array{message: array, model: string, provider: string, usage: array, sources: ?array, latencyMs: int}
     */
    public function rag(
        string $model,
        array $messages,
        string $namespace,
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?int $limit = null,
        ?float $threshold = null,
        ?bool $includeSources = null,
    ): array {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'namespace' => $namespace,
            'stream' => false,
        ];

        if ($temperature !== null) {
            $payload['temperature'] = $temperature;
        }
        if ($maxTokens !== null) {
            $payload['maxTokens'] = $maxTokens;
        }
        $payload['limit'] = $limit ?? 5;
        $payload['threshold'] = $threshold ?? 0.5;
        $payload['include_sources'] = $includeSources ?? true;

        $data = $this->client->request('/api/ai/rag', 'POST', $payload);

        $sources = null;
        if (isset($data['sources'])) {
            $sources = [];
            foreach ($data['sources'] as $s) {
                $sources[] = [
                    'content' => $s['content'] ?? '',
                    'metadata' => $s['metadata'] ?? [],
                    'similarity' => $s['similarity'] ?? 0.0,
                ];
            }
        }

        return [
            'message' => [
                'role' => $data['message']['role'] ?? 'assistant',
                'content' => $data['message']['content'] ?? '',
            ],
            'model' => $data['model'] ?? $model,
            'provider' => $data['provider'] ?? '',
            'usage' => [
                'promptTokens' => $data['usage']['prompt_tokens'] ?? 0,
                'completionTokens' => $data['usage']['completion_tokens'] ?? 0,
                'totalTokens' => $data['usage']['total_tokens'] ?? 0,
            ],
            'sources' => $sources,
            'latencyMs' => $data['latency_ms'] ?? 0,
        ];
    }

    /**
     * Stream RAG responses with automatic memory context.
     *
     * @param string $model AI model to use
     * @param array $messages List of message arrays
     * @param string $namespace Memory namespace to search
     * @param float|null $temperature Sampling temperature (0-1)
     * @param int|null $maxTokens Maximum tokens in response
     * @param int|null $limit Number of memories to retrieve (default: 5)
     * @param float|null $threshold Similarity threshold (default: 0.5)
     * @param bool|null $includeSources Whether to include source documents (default: true)
     * @return \Generator
     */
    public function ragStream(
        string $model,
        array $messages,
        string $namespace,
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?int $limit = null,
        ?float $threshold = null,
        ?bool $includeSources = null,
    ): \Generator {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'namespace' => $namespace,
            'stream' => true,
        ];

        if ($temperature !== null) {
            $payload['temperature'] = $temperature;
        }
        if ($maxTokens !== null) {
            $payload['maxTokens'] = $maxTokens;
        }
        $payload['limit'] = $limit ?? 5;
        $payload['threshold'] = $threshold ?? 0.5;
        $payload['include_sources'] = $includeSources ?? true;

        $build = $this->client->buildRequestOptions('POST', $payload);
        $url = $this->client->getBaseUrl() . '/api/ai/rag';

        $response = $this->client->getHttpClient()->request('POST', $url, array_merge(
            $build['options'],
            ['stream' => true, 'timeout' => 60]
        ));

        $statusCode = $response->getStatusCode();
        if ($statusCode === 401) {
            throw new \Cencori\Errors\AuthenticationError();
        }
        if ($statusCode === 429) {
            throw new \Cencori\Errors\RateLimitError();
        }
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \Cencori\Errors\CencoriError(
                message: "Request failed with status $statusCode",
                statusCode: $statusCode,
            );
        }

        $body = $response->getBody();
        while (!$body->eof()) {
            $line = $this->readLine($body);
            if ($line === null) {
                break;
            }

            $line = trim($line);
            if (str_starts_with($line, 'data: ')) {
                $dataStr = substr($line, 6);

                if ($dataStr === '[DONE]') {
                    break;
                }

                $data = json_decode($dataStr, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }

                yield $data;
            }
        }
    }

    /**
     * Send a request to the OpenAI-compatible Responses API.
     *
     * Supports built-in tools: web_search_preview, file_search, code_interpreter.
     *
     * @param string $model AI model to use
     * @param string|array $input Input string or array of response input items
     * @param string|null $instructions System instructions
     * @param array|null $tools Tools for the model
     * @param mixed $toolChoice Tool choice configuration
     * @param float|null $temperature Sampling temperature
     * @param int|null $maxOutputTokens Maximum output tokens
     * @param float|null $topP Top-p sampling
     * @param bool|null $store Whether to store the response
     * @param array|null $metadata Metadata to attach
     * @param string|null $previousResponseId Previous response ID for continuation
     * @param bool|null $parallelToolCalls Allow parallel tool calls
     * @param string|null $truncation Truncation strategy
     * @param array|null $responseFormat Response format configuration
     * @param array|null $include Additional fields to include
     * @param string|null $user User identifier
     * @return array
     */
    public function responses(
        string $model,
        string|array $input,
        ?string $instructions = null,
        ?array $tools = null,
        mixed $toolChoice = null,
        ?float $temperature = null,
        ?int $maxOutputTokens = null,
        ?float $topP = null,
        ?bool $store = null,
        ?array $metadata = null,
        ?string $previousResponseId = null,
        ?bool $parallelToolCalls = null,
        ?string $truncation = null,
        ?array $responseFormat = null,
        ?array $include = null,
        ?string $user = null,
    ): array {
        $payload = [
            'model' => $model,
            'input' => $input,
            'stream' => false,
        ];

        if ($instructions !== null) {
            $payload['instructions'] = $instructions;
        }
        if ($tools !== null) {
            $payload['tools'] = $tools;
        }
        if ($toolChoice !== null) {
            $payload['tool_choice'] = $toolChoice;
        }
        if ($temperature !== null) {
            $payload['temperature'] = $temperature;
        }
        if ($maxOutputTokens !== null) {
            $payload['max_output_tokens'] = $maxOutputTokens;
        }
        if ($topP !== null) {
            $payload['top_p'] = $topP;
        }
        if ($store !== null) {
            $payload['store'] = $store;
        }
        if ($metadata !== null) {
            $payload['metadata'] = $metadata;
        }
        if ($previousResponseId !== null) {
            $payload['previous_response_id'] = $previousResponseId;
        }
        if ($parallelToolCalls !== null) {
            $payload['parallel_tool_calls'] = $parallelToolCalls;
        }
        if ($truncation !== null) {
            $payload['truncation'] = $truncation;
        }
        if ($responseFormat !== null) {
            $payload['response_format'] = $responseFormat;
        }
        if ($include !== null) {
            $payload['include'] = $include;
        }
        if ($user !== null) {
            $payload['user'] = $user;
        }

        return $this->client->request('/v1/responses', 'POST', $payload);
    }

    /**
     * Stream responses from the Responses API via SSE.
     *
     * @param string $model AI model to use
     * @param string|array $input Input string or array of response input items
     * @param string|null $instructions System instructions
     * @param array|null $tools Tools for the model
     * @param mixed $toolChoice Tool choice configuration
     * @param float|null $temperature Sampling temperature
     * @param int|null $maxOutputTokens Maximum output tokens
     * @param float|null $topP Top-p sampling
     * @param bool|null $store Whether to store the response
     * @param array|null $metadata Metadata to attach
     * @param string|null $previousResponseId Previous response ID for continuation
     * @param bool|null $parallelToolCalls Allow parallel tool calls
     * @param string|null $truncation Truncation strategy
     * @param array|null $responseFormat Response format configuration
     * @param array|null $include Additional fields to include
     * @param string|null $user User identifier
     * @return \Generator
     */
    public function responsesStream(
        string $model,
        string|array $input,
        ?string $instructions = null,
        ?array $tools = null,
        mixed $toolChoice = null,
        ?float $temperature = null,
        ?int $maxOutputTokens = null,
        ?float $topP = null,
        ?bool $store = null,
        ?array $metadata = null,
        ?string $previousResponseId = null,
        ?bool $parallelToolCalls = null,
        ?string $truncation = null,
        ?array $responseFormat = null,
        ?array $include = null,
        ?string $user = null,
    ): \Generator {
        $payload = [
            'model' => $model,
            'input' => $input,
            'stream' => true,
        ];

        if ($instructions !== null) {
            $payload['instructions'] = $instructions;
        }
        if ($tools !== null) {
            $payload['tools'] = $tools;
        }
        if ($toolChoice !== null) {
            $payload['tool_choice'] = $toolChoice;
        }
        if ($temperature !== null) {
            $payload['temperature'] = $temperature;
        }
        if ($maxOutputTokens !== null) {
            $payload['max_output_tokens'] = $maxOutputTokens;
        }
        if ($topP !== null) {
            $payload['top_p'] = $topP;
        }
        if ($store !== null) {
            $payload['store'] = $store;
        }
        if ($metadata !== null) {
            $payload['metadata'] = $metadata;
        }
        if ($previousResponseId !== null) {
            $payload['previous_response_id'] = $previousResponseId;
        }
        if ($parallelToolCalls !== null) {
            $payload['parallel_tool_calls'] = $parallelToolCalls;
        }
        if ($truncation !== null) {
            $payload['truncation'] = $truncation;
        }
        if ($responseFormat !== null) {
            $payload['response_format'] = $responseFormat;
        }
        if ($include !== null) {
            $payload['include'] = $include;
        }
        if ($user !== null) {
            $payload['user'] = $user;
        }

        $build = $this->client->buildRequestOptions('POST', $payload);
        $url = $this->client->getBaseUrl() . '/v1/responses';

        $response = $this->client->getHttpClient()->request('POST', $url, array_merge(
            $build['options'],
            ['stream' => true, 'timeout' => 60]
        ));

        $statusCode = $response->getStatusCode();
        if ($statusCode === 401) {
            throw new \Cencori\Errors\AuthenticationError();
        }
        if ($statusCode === 429) {
            throw new \Cencori\Errors\RateLimitError();
        }
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \Cencori\Errors\CencoriError(
                message: "Request failed with status $statusCode",
                statusCode: $statusCode,
            );
        }

        $body = $response->getBody();
        $buffer = '';
        $eventType = '';

        while (!$body->eof()) {
            $line = $this->readLine($body);
            if ($line === null) {
                break;
            }

            $line = trim($line);
            if ($line === '') {
                $eventType = '';
                continue;
            }
            if (str_starts_with($line, 'event: ')) {
                $eventType = substr($line, 7);
                continue;
            }
            if (str_starts_with($line, 'data: ')) {
                $dataStr = substr($line, 6);
                $data = json_decode($dataStr, true);
                if (json_last_error() === JSON_ERROR_NONE && $data !== null) {
                    yield [
                        'type' => $eventType ?: 'message',
                        'data' => $data,
                    ];
                }
                $eventType = '';
            }
        }
    }
}
