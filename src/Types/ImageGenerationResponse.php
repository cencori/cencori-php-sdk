<?php

namespace Cencori\Types;

/**
 * Image generation response.
 */
class ImageGenerationResponse
{
    public function __construct(
        public readonly array $images,
        public readonly string $model,
        public readonly string $provider,
    ) {}

    public static function fromArray(array $data): self
    {
        $images = [];
        foreach ($data['images'] ?? [] as $img) {
            $images[] = GeneratedImage::fromArray($img);
        }

        return new self(
            images: $images,
            model: $data['model'] ?? '',
            provider: $data['provider'] ?? '',
        );
    }
}
