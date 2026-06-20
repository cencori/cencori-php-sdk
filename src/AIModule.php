<?php

namespace Cencori;

use Cencori\Types\{
    ChatResponse,
    EmbeddingResponse,
    Message,
    StreamChunk,
};

/**
 * AI module for chat completions, embeddings, and streaming.
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
     * @return ChatResponse
     */
    public function chat(
        array $messages,
        string $model = 'gemini-2.5-flash',
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?string $userId = null,
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
     * @return \Generator|StreamChunk[]
     */
    public function chatStream(
        array $messages,
        string $model = 'gemini-2.5-flash',
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?string $userId = null,
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

        $url = $this->client->getBaseUrl() . '/api/ai/chat';
        $headers = [
            'Content-Type' => 'application/json',
            'CENCORI_API_KEY' => $this->client->getApiKey(),
        ];

        $response = $this->client->getHttpClient()->request('POST', $url, [
            'headers' => $headers,
            'json' => $payload,
            'stream' => true,
            'timeout' => 60,
        ]);

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
                $dataStr = substr($line, 6); // Remove "data: " prefix

                if ($dataStr === '[DONE]') {
                    break;
                }

                $data = json_decode($dataStr, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }

                // Check for error in stream
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

        $data = $this->client->request('/api/v1/embeddings', 'POST', $payload);
        return EmbeddingResponse::fromArray($data, $model);
    }
}
