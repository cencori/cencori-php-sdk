<?php

namespace Cencori\Types;

class RequestMetrics
{
    public function __construct(
        public readonly int $total,
        public readonly int $success,
        public readonly int $error,
        public readonly int $filtered,
        public readonly float $successRate,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            total: $data['total'] ?? 0,
            success: $data['success'] ?? 0,
            error: $data['error'] ?? 0,
            filtered: $data['filtered'] ?? 0,
            successRate: (float)($data['success_rate'] ?? 0.0),
        );
    }
}
