<?php

namespace Cencori\Types;

/**
 * Options for storing a memory.
 */
class StoreMemoryOptions
{
    public function __construct(
        public readonly string $namespace,
        public readonly string $content,
        public readonly ?array $embedding = null,
        public readonly ?array $metadata = null,
        public readonly string|\DateTimeInterface|null $expiresAt = null,
    ) {}

    public function toArray(): array
    {
        $result = [
            'namespace' => $this->namespace,
            'content' => $this->content,
        ];
        if ($this->embedding !== null) {
            $result['embedding'] = $this->embedding;
        }
        if ($this->metadata !== null) {
            $result['metadata'] = $this->metadata;
        }
        if ($this->expiresAt !== null) {
            $result['expiresAt'] = $this->expiresAt instanceof \DateTimeInterface
                ? $this->expiresAt->format(\DateTimeInterface::ATOM)
                : $this->expiresAt;
        }
        return $result;
    }
}
