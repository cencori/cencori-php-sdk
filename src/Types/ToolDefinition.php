<?php

namespace Cencori\Types;

/**
 * Tool/function definition for AI models.
 */
class ToolDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?array $parameters = null,
    ) {}

    public function toArray(): array
    {
        $def = [
            'type' => 'function',
            'function' => [
                'name' => $this->name,
            ],
        ];

        if ($this->description !== null) {
            $def['function']['description'] = $this->description;
        }
        if ($this->parameters !== null) {
            $def['function']['parameters'] = $this->parameters;
        }

        return $def;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['function']['name'] ?? '',
            description: $data['function']['description'] ?? null,
            parameters: $data['function']['parameters'] ?? null,
        );
    }
}
