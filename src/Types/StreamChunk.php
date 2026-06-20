<?php

namespace Cencori\Types;

class StreamChunk
{
    public function __construct(
        public readonly string $delta,
        public readonly ?string $finishReason = null,
        public readonly ?string $error = null,
        public readonly ?array $toolCalls = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            delta: $data['delta'] ?? '',
            finishReason: $data['finish_reason'] ?? null,
            error: $data['error'] ?? null,
            toolCalls: $data['toolCalls'] ?? null,
        );
    }
}
