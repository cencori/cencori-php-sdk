<?php

namespace Cencori\Types;

class ChatResponse
{
    public function __construct(
        public readonly string $content,
        public readonly string $model,
        public readonly string $provider,
        public readonly Usage $usage,
        public readonly float $costUsd,
        public readonly string $finishReason,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'] ?? '',
            model: $data['model'] ?? '',
            provider: $data['provider'] ?? '',
            usage: Usage::fromArray($data['usage'] ?? []),
            costUsd: (float)($data['cost_usd'] ?? 0.0),
            finishReason: $data['finish_reason'] ?? '',
        );
    }
}
