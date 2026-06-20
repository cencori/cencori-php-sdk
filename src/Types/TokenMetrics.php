<?php

namespace Cencori\Types;

class TokenMetrics
{
    public function __construct(
        public readonly int $prompt,
        public readonly int $completion,
        public readonly int $total,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            prompt: $data['prompt'] ?? 0,
            completion: $data['completion'] ?? 0,
            total: $data['total'] ?? 0,
        );
    }
}
