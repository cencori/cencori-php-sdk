<?php

namespace Cencori\Types;

/**
 * Parameters for creating an agent API key.
 */
class CreateAgentKeyParams
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $environment = null,
        public readonly ?string $keyType = null,
        public readonly ?array $allowedDomains = null,
    ) {}

    public function toArray(): array
    {
        $result = [];
        if ($this->name !== null) {
            $result['name'] = $this->name;
        }
        if ($this->environment !== null) {
            $result['environment'] = $this->environment;
        }
        if ($this->keyType !== null) {
            $result['key_type'] = $this->keyType;
        }
        if ($this->allowedDomains !== null) {
            $result['allowed_domains'] = $this->allowedDomains;
        }
        return $result;
    }
}
