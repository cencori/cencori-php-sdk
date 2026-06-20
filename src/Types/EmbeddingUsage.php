<?php

namespace Cencori\Types;

class EmbeddingUsage
{
    public function __construct(
        public readonly int $totalTokens,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalTokens: $data['total_tokens'] ?? 0,
        );
    }
}
