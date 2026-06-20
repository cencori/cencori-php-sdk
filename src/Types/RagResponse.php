<?php

namespace Cencori\Types;

/**
 * RAG response with AI answer and source documents.
 */
class RagResponse
{
    public function __construct(
        public readonly array $message,
        public readonly string $model,
        public readonly string $provider,
        public readonly array $usage,
        public readonly ?array $sources = null,
        public readonly int $latencyMs = 0,
    ) {}

    public static function fromArray(array $data): self
    {
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

        return new self(
            message: [
                'role' => $data['message']['role'] ?? 'assistant',
                'content' => $data['message']['content'] ?? '',
            ],
            model: $data['model'] ?? '',
            provider: $data['provider'] ?? '',
            usage: [
                'promptTokens' => $data['usage']['prompt_tokens'] ?? 0,
                'completionTokens' => $data['usage']['completion_tokens'] ?? 0,
                'totalTokens' => $data['usage']['total_tokens'] ?? 0,
            ],
            sources: $sources,
            latencyMs: $data['latency_ms'] ?? 0,
        );
    }
}
