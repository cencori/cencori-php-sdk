<?php

namespace Cencori;

/**
 * Vision module — analyze, describe, OCR, and classify images.
 *
 * All methods accept either an ``image_url`` (https:// or data:) or an
 * ``image_base64`` (with ``mime_type``). Provide exactly one.
 *
 * @example
 * // From a URL
 * $result = $cencori->vision->analyze([
 *     'image_url' => 'https://example.com/photo.jpg',
 *     'prompt' => 'What breed of dog is this?',
 * ]);
 * echo $result['analysis'];
 *
 * @example
 * // From a local file
 * $b64 = base64_encode(file_get_contents('receipt.png'));
 * $ocr = $cencori->vision->ocr(['image_base64' => $b64, 'mime_type' => 'image/png']);
 * echo $ocr['text'];
 */
class VisionModule
{
    private Cencori $client;

    public function __construct(Cencori $client)
    {
        $this->client = $client;
    }

    /**
     * General image analysis with an optional custom prompt.
     *
     * @param array $params Vision parameters:
     *   - image_url: ?string
     *   - image_base64: ?string
     *   - mime_type: ?string (required with image_base64)
     *   - prompt: ?string (defaults to "Describe this image in detail.")
     *   - model: ?string (defaults to gpt-4o-mini)
     *   - max_tokens: ?int
     *   - temperature: ?float
     *   - response_format: ?"text"|"json"
     * @return array{analysis: string, model: string, provider: string, usage: array, cost: array}
     */
    public function analyze(array $params): array
    {
        return $this->client->request('/api/ai/vision', 'POST', $this->buildBody($params));
    }

    /**
     * Describe an image in rich detail.
     *
     * @param array $params See analyze(). "prompt" is ignored (preset used).
     * @return array{description: string, model: string, provider: string, usage: array, cost: array}
     */
    public function describe(array $params): array
    {
        return $this->client->request('/api/ai/vision/describe', 'POST', $this->buildBody($params));
    }

    /**
     * Extract all text visible in an image.
     *
     * @param array $params See analyze().
     * @return array{text: string, model: string, provider: string, usage: array, cost: array}
     */
    public function ocr(array $params): array
    {
        return $this->client->request('/api/ai/vision/ocr', 'POST', $this->buildBody($params));
    }

    /**
     * Classify an image and return structured tags + categories.
     *
     * @param array $params See analyze().
     * @return array{classification: array|string, raw: string, model: string, provider: string, usage: array, cost: array}
     */
    public function classify(array $params): array
    {
        return $this->client->request('/api/ai/vision/classify', 'POST', $this->buildBody($params));
    }

    /**
     * @throws \InvalidArgumentException when neither image_url nor image_base64 is provided
     */
    private function buildBody(array $params): array
    {
        $imageUrl = $params['image_url'] ?? null;
        $imageBase64 = $params['image_base64'] ?? null;
        $images = $params['images'] ?? null;

        if (!$imageUrl && !$imageBase64 && (!is_array($images) || count($images) === 0)) {
            throw new \InvalidArgumentException(
                'vision request requires image_url, image_base64, or images'
            );
        }

        $body = [];
        if (is_array($images) && count($images) > 0) {
            $body['images'] = $images;
        } elseif ($imageUrl) {
            $body['image_url'] = $imageUrl;
        } else {
            $body['image_base64'] = $imageBase64;
            if (!empty($params['mime_type'])) {
                $body['mime_type'] = $params['mime_type'];
            }
        }

        foreach (['prompt', 'model', 'response_format'] as $key) {
            if (array_key_exists($key, $params) && $params[$key] !== null) {
                $body[$key] = $params[$key];
            }
        }
        if (isset($params['max_tokens'])) {
            $body['max_tokens'] = (int) $params['max_tokens'];
        }
        if (isset($params['temperature'])) {
            $body['temperature'] = (float) $params['temperature'];
        }

        return $body;
    }
}
