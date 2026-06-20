<?php

namespace Cencori\Types;

class DailyStat
{
    public function __construct(
        public readonly string $date,
        public readonly int $count,
        public readonly float $costUsd,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            date: $data['date'] ?? '',
            count: $data['count'] ?? 0,
            costUsd: (float)($data['cost_usd'] ?? 0.0),
        );
    }
}
