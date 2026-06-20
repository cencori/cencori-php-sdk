<?php

namespace Cencori\Types;

/**
 * Structured output response.
 */
class GenerateObjectResponse
{
    public function __construct(
        public readonly mixed $object,
        public readonly int $promptTokens = 0,
        public readonly int $completionTokens = 0,
        public readonly int $totalTokens = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            object: $data['object'] ?? null,
            promptTokens: $data['usage']['promptTokens'] ?? $data['usage']['prompt_tokens'] ?? 0,
            completionTokens: $data['usage']['completionTokens'] ?? $data['usage']['completion_tokens'] ?? 0,
            totalTokens: $data['usage']['totalTokens'] ?? $data['usage']['total_tokens'] ?? 0,
        );
    }
}
