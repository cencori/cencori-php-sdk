<?php

namespace Cencori\Types;

class Stats
{
    public function __construct(
        public readonly int $totalRequests,
        public readonly float $totalCostUsd,
        public readonly ?string $lastUsedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalRequests: $data['total_requests'] ?? 0,
            totalCostUsd: (float)($data['total_cost_usd'] ?? 0.0),
            lastUsedAt: $data['last_used_at'] ?? null,
        );
    }
}
