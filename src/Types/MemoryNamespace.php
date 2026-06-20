<?php

namespace Cencori\Types;

/**
 * A memory namespace for organizing vector memories.
 */
class MemoryNamespace
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $embeddingModel,
        public readonly int $dimensions,
        public readonly string $createdAt,
        public readonly ?string $description = null,
        public readonly array $metadata = [],
        public readonly ?int $memoryCount = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            embeddingModel: $data['embeddingModel'] ?? $data['embedding_model'] ?? '',
            dimensions: $data['dimensions'] ?? 0,
            createdAt: $data['createdAt'] ?? $data['created_at'] ?? '',
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? [],
            memoryCount: $data['memoryCount'] ?? $data['memory_count'] ?? null,
        );
    }
}
