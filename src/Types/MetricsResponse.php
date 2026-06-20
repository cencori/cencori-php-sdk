<?php

namespace Cencori\Types;

class MetricsResponse
{
    public function __construct(
        public readonly string $period,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly RequestMetrics $requests,
        public readonly CostMetrics $cost,
        public readonly TokenMetrics $tokens,
        public readonly LatencyMetrics $latency,
        public readonly array $providers,
        public readonly array $models,
    ) {}

    public static function fromArray(array $data): self
    {
        $providers = [];
        foreach ($data['providers'] ?? [] as $key => $value) {
            $providers[$key] = Breakdown::fromArray($value);
        }

        $models = [];
        foreach ($data['models'] ?? [] as $key => $value) {
            $models[$key] = Breakdown::fromArray($value);
        }

        return new self(
            period: $data['period'] ?? '',
            startDate: $data['start_date'] ?? '',
            endDate: $data['end_date'] ?? '',
            requests: RequestMetrics::fromArray($data['requests'] ?? []),
            cost: CostMetrics::fromArray($data['cost'] ?? []),
            tokens: TokenMetrics::fromArray($data['tokens'] ?? []),
            latency: LatencyMetrics::fromArray($data['latency'] ?? []),
            providers: $providers,
            models: $models,
        );
    }
}
