<?php

namespace Cencori\Types;

class APIKey
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $environment,
        public readonly string $createdAt,
        public readonly ?string $prefix = null,
        public readonly ?string $key = null,
        public readonly ?string $lastUsedAt = null,
        public readonly ?int $usageCount = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            environment: $data['environment'] ?? '',
            createdAt: $data['created_at'] ?? '',
            prefix: $data['prefix'] ?? null,
            key: $data['key'] ?? null,
            lastUsedAt: $data['last_used_at'] ?? null,
            usageCount: $data['usage_count'] ?? null,
        );
    }
}
