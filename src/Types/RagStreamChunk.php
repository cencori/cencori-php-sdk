<?php

namespace Cencori\Types;

/**
 * A single chunk from a streaming RAG response.
 */
class RagStreamChunk
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $delta = null,
        public readonly ?string $finishReason = null,
        public readonly ?array $sources = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'content',
            delta: $data['delta'] ?? null,
            finishReason: $data['finish_reason'] ?? null,
            sources: $data['sources'] ?? null,
        );
    }
}
