<?php

namespace Cencori\Types;

class CostMetrics
{
    public function __construct(
        public readonly float $totalUsd,
        public readonly float $averagePerRequestUsd,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalUsd: (float)($data['total_usd'] ?? 0.0),
            averagePerRequestUsd: (float)($data['average_per_request_usd'] ?? 0.0),
        );
    }
}
