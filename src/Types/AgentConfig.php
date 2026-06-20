<?php

namespace Cencori\Types;

/**
 * Agent configuration.
 */
class AgentConfig
{
    public function __construct(
        public readonly string $model = '',
        public readonly ?string $systemPrompt = null,
        public readonly array $tools = [],
        public readonly ?float $temperature = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            model: $data['model'] ?? '',
            systemPrompt: $data['system_prompt'] ?? null,
            tools: $data['tools'] ?? [],
            temperature: $data['temperature'] ?? null,
        );
    }

    public function toArray(): array
    {
        $result = [];
        if ($this->model !== '') {
            $result['model'] = $this->model;
        }
        if ($this->systemPrompt !== null) {
            $result['system_prompt'] = $this->systemPrompt;
        }
        if (!empty($this->tools)) {
            $result['tools'] = $this->tools;
        }
        if ($this->temperature !== null) {
            $result['temperature'] = $this->temperature;
        }
        return $result;
    }
}
