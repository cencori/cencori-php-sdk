<?php

namespace Cencori\Types;

/**
 * Options for searching memories.
 */
class SearchMemoryOptions
{
    public function __construct(
        public readonly string $namespace,
        public readonly string $query,
        public readonly ?int $limit = null,
        public readonly ?float $threshold = null,
        public readonly ?array $filter = null,
    ) {}

    public function toArray(): array
    {
        $result = [
            'namespace' => $this->namespace,
            'query' => $this->query,
        ];
        if ($this->limit !== null) {
            $result['limit'] = $this->limit;
        }
        if ($this->threshold !== null) {
            $result['threshold'] = $this->threshold;
        }
        if ($this->filter !== null) {
            $result['filter'] = $this->filter;
        }
        return $result;
    }
}
