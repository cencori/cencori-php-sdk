<?php

namespace Cencori\Types;

class EmbeddingResponse
{
    public function __construct(
        public readonly string $model,
        public readonly array $embeddings,
        public readonly EmbeddingUsage $usage,
    ) {}

    public static function fromArray(array $data, string $model): self
    {
        $embeddings = [];
        foreach ($data['data'] ?? [] as $item) {
            $embeddings[] = $item['embedding'] ?? [];
        }

        return new self(
            model: $data['model'] ?? $model,
            embeddings: $embeddings,
            usage: EmbeddingUsage::fromArray($data['usage'] ?? []),
        );
    }
}
