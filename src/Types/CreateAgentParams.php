<?php

namespace Cencori\Types;

/**
 * Parameters for creating a new agent.
 */
class CreateAgentParams
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?AgentConfig $config = null,
    ) {}

    public function toArray(): array
    {
        $result = ['name' => $this->name];
        if ($this->description !== null) {
            $result['description'] = $this->description;
        }
        if ($this->config !== null) {
            $result['config'] = $this->config->toArray();
        }
        return $result;
    }
}
