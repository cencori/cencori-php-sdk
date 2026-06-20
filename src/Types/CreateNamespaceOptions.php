<?php

namespace Cencori\Types;

/**
 * Options for creating a new memory namespace.
 */
class CreateNamespaceOptions
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $embeddingModel = null,
        public readonly ?int $dimensions = null,
        public readonly ?array $metadata = null,
    ) {}

    public function toArray(): array
    {
        $result = ['name' => $this->name];
        if ($this->description !== null) {
            $result['description'] = $this->description;
        }
        if ($this->embeddingModel !== null) {
            $result['embeddingModel'] = $this->embeddingModel;
        }
        if ($this->dimensions !== null) {
            $result['dimensions'] = $this->dimensions;
        }
        if ($this->metadata !== null) {
            $result['metadata'] = $this->metadata;
        }
        return $result;
    }
}
