<?php

namespace Cencori\Types;

/**
 * A single generated image.
 */
class GeneratedImage
{
    public function __construct(
        public readonly ?string $url = null,
        public readonly ?string $b64Json = null,
        public readonly ?string $revisedPrompt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'] ?? null,
            b64Json: $data['b64_json'] ?? $data['b64Json'] ?? null,
            revisedPrompt: $data['revisedPrompt'] ?? null,
        );
    }
}
