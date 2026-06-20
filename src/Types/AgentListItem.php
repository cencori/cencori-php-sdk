<?php

namespace Cencori\Types;

/**
 * A lightweight agent summary for list operations.
 */
class AgentListItem
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly bool $isActive = false,
        public readonly bool $shadowMode = false,
        public readonly string $createdAt = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            description: $data['description'] ?? null,
            isActive: $data['is_active'] ?? false,
            shadowMode: $data['shadow_mode'] ?? false,
            createdAt: $data['created_at'] ?? '',
        );
    }
}
