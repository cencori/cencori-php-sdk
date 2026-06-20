<?php

namespace Cencori\Types;

class Project
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $description,
        public readonly string $status,
        public readonly string $visibility,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?Stats $stats = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? '',
            description: $data['description'] ?? '',
            status: $data['status'] ?? '',
            visibility: $data['visibility'] ?? '',
            createdAt: $data['created_at'] ?? '',
            updatedAt: $data['updated_at'] ?? '',
            stats: isset($data['stats']) ? Stats::fromArray($data['stats']) : null,
        );
    }
}
