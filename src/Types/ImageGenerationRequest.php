<?php

namespace Cencori\Types;

/**
 * Image generation request parameters.
 */
class ImageGenerationRequest
{
    public function __construct(
        public readonly string $prompt,
        public readonly string $model = 'dall-e-3',
        public readonly ?int $n = null,
        public readonly ?string $size = null,
        public readonly ?string $quality = null,
        public readonly ?string $style = null,
        public readonly ?string $responseFormat = null,
    ) {}

    public function toArray(): array
    {
        $result = [
            'prompt' => $this->prompt,
            'model' => $this->model,
        ];

        if ($this->n !== null) {
            $result['n'] = $this->n;
        }
        if ($this->size !== null) {
            $result['size'] = $this->size;
        }
        if ($this->quality !== null) {
            $result['quality'] = $this->quality;
        }
        if ($this->style !== null) {
            $result['style'] = $this->style;
        }
        if ($this->responseFormat !== null) {
            $result['responseFormat'] = $this->responseFormat;
        }

        return $result;
    }
}
