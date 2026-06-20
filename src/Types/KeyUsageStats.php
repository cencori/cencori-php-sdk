<?php

namespace Cencori\Types;

class KeyUsageStats
{
    public function __construct(
        public readonly string $keyId,
        public readonly int $totalRequests,
        public readonly float $totalCostUsd,
        public readonly string $lastUsedAt,
        public readonly array $requestsByDay,
        public readonly array $requestsByModel,
    ) {}

    public static function fromArray(array $data): self
    {
        $requestsByDay = [];
        foreach ($data['requests_by_day'] ?? [] as $d) {
            $requestsByDay[] = DailyStat::fromArray($d);
        }

        return new self(
            keyId: $data['key_id'] ?? '',
            totalRequests: $data['total_requests'] ?? 0,
            totalCostUsd: (float)($data['total_cost_usd'] ?? 0.0),
            lastUsedAt: $data['last_used_at'] ?? '',
            requestsByDay: $requestsByDay,
            requestsByModel: $data['requests_by_model'] ?? [],
        );
    }
}
