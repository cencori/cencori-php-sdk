<?php

namespace Cencori\Types;

class LatencyMetrics
{
    public function __construct(
        public readonly int $avgMs,
        public readonly int $p50Ms,
        public readonly int $p90Ms,
        public readonly int $p99Ms,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            avgMs: $data['avg_ms'] ?? 0,
            p50Ms: $data['p50_ms'] ?? 0,
            p90Ms: $data['p90_ms'] ?? 0,
            p99Ms: $data['p99_ms'] ?? 0,
        );
    }
}
