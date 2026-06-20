<?php

namespace Cencori\Types;

/**
 * Result of a memory search.
 */
class SearchResult
{
    public function __construct(
        public readonly array $results,
        public readonly string $query,
        public readonly string $namespace,
        public readonly int $count,
        public readonly int $latencyMs,
    ) {}

    public static function fromArray(array $data): self
    {
        $memories = [];
        foreach ($data['results'] ?? [] as $r) {
            $memories[] = Memory::fromArray($r);
        }

        return new self(
            results: $memories,
            query: $data['query'] ?? '',
            namespace: $data['namespace'] ?? '',
            count: $data['count'] ?? 0,
            latencyMs: $data['latencyMs'] ?? $data['latency_ms'] ?? 0,
        );
    }
}
