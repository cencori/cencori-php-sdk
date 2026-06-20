<?php

namespace Cencori\Types;

/**
 * Parameters for updating an agent.
 */
class UpdateAgentParams
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?bool $isActive = null,
        public readonly ?bool $shadowMode = null,
        public readonly ?AgentConfig $config = null,
    ) {}

    public function toArray(): array
    {
        $result = [];
        if ($this->name !== null) {
            $result['name'] = $this->name;
        }
        if ($this->description !== null) {
            $result['description'] = $this->description;
        }
        if ($this->isActive !== null) {
            $result['is_active'] = $this->isActive;
        }
        if ($this->shadowMode !== null) {
            $result['shadow_mode'] = $this->shadowMode;
        }
        if ($this->config !== null) {
            $result['config'] = $this->config->toArray();
        }
        return $result;
    }
}
