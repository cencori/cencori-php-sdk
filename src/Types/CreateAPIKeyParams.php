<?php

namespace Cencori\Types;

class CreateAPIKeyParams
{
    public function __construct(
        public readonly string $name,
        public readonly string $environment,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'environment' => $this->environment,
        ];
    }
}
