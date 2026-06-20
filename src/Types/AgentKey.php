<?php

namespace Cencori\Types;

/**
 * An API key for an agent.
 */
class AgentKey
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $keyPrefix,
        public readonly string $environment,
        public readonly string $keyType,
        public readonly string $agentId,
        public readonly string $createdAt,
        public readonly ?string $fullKey = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            keyPrefix: $data['key_prefix'] ?? '',
            environment: $data['environment'] ?? '',
            keyType: $data['key_type'] ?? '',
            agentId: $data['agent_id'] ?? '',
            createdAt: $data['created_at'] ?? '',
            fullKey: $data['full_key'] ?? null,
        );
    }
}
