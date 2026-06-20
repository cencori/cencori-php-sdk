<?php

namespace Cencori\Types;

class CreateProjectParams
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $visibility = null,
    ) {}

    public function toArray(): array
    {
        $result = ['name' => $this->name];
        if ($this->description !== null) {
            $result['description'] = $this->description;
        }
        if ($this->visibility !== null) {
            $result['visibility'] = $this->visibility;
        }
        return $result;
    }
}
