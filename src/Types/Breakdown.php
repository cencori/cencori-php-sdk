<?php

namespace Cencori\Types;

class Breakdown
{
    public function __construct(
        public readonly int $requests,
        public readonly float $costUsd,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requests: $data['requests'] ?? 0,
            costUsd: (float)($data['cost_usd'] ?? 0.0),
        );
    }
}
