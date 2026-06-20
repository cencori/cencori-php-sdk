<?php

namespace Cencori\Types;

/**
 * A stored memory with content and metadata.
 */
class Memory
{
    public function __construct(
        public readonly string $id,
        public readonly string $namespace,
        public readonly string $content,
        public readonly string $createdAt,
        public readonly array $metadata = [],
        public readonly ?float $similarity = null,
        public readonly ?string $expiresAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            namespace: $data['namespace'] ?? '',
            content: $data['content'] ?? '',
            createdAt: $data['createdAt'] ?? $data['created_at'] ?? '',
            metadata: $data['metadata'] ?? [],
            similarity: $data['similarity'] ?? null,
            expiresAt: $data['expiresAt'] ?? $data['expires_at'] ?? null,
            updatedAt: $data['updatedAt'] ?? $data['updated_at'] ?? null,
        );
    }
}
