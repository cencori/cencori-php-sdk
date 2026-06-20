<?php

namespace Cencori\Types;

/**
 * A tool call made by the model.
 */
class ToolCall
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $arguments,
        public readonly string $type = 'function',
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'function' => [
                'name' => $this->name,
                'arguments' => $this->arguments,
            ],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['function']['name'] ?? '',
            arguments: $data['function']['arguments'] ?? '',
            type: $data['type'] ?? 'function',
        );
    }
}
