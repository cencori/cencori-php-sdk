<?php

namespace Cencori\Types;

/**
 * RAG (Retrieval-Augmented Generation) request parameters.
 */
class RagRequest
{
    public function __construct(
        public readonly string $model,
        public readonly array $messages,
        public readonly string $namespace,
        public readonly ?float $temperature = null,
        public readonly ?int $maxTokens = null,
        public readonly int $limit = 5,
        public readonly float $threshold = 0.5,
        public readonly bool $includeSources = true,
    ) {}

    public function toArray(): array
    {
        $result = [
            'model' => $this->model,
            'messages' => $this->messages,
            'namespace' => $this->namespace,
            'limit' => $this->limit,
            'threshold' => $this->threshold,
            'include_sources' => $this->includeSources,
            'stream' => false,
        ];

        if ($this->temperature !== null) {
            $result['temperature'] = $this->temperature;
        }
        if ($this->maxTokens !== null) {
            $result['maxTokens'] = $this->maxTokens;
        }

        return $result;
    }
}
