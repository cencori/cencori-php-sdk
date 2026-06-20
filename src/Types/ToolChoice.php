<?php

namespace Cencori\Types;

/**
 * Tool choice configuration for AI models.
 *
 * Can be 'auto', 'none', 'required', or a specific function call.
 */
class ToolChoice
{
    private function __construct(
        private readonly string|array $value,
    ) {}

    public static function auto(): self
    {
        return new self('auto');
    }

    public static function none(): self
    {
        return new self('none');
    }

    public static function required(): self
    {
        return new self('required');
    }

    public static function function(string $name): self
    {
        return new self([
            'type' => 'function',
            'function' => ['name' => $name],
        ]);
    }

    public function toValue(): string|array
    {
        return $this->value;
    }
}
