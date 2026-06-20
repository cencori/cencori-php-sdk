<?php

namespace Cencori\Types;

/**
 * Structured output request with JSON schema.
 */
class GenerateObjectRequest
{
    public function __construct(
        public readonly string $model,
        public readonly array $schema,
        public readonly ?string $prompt = null,
        public readonly ?array $messages = null,
        public readonly ?string $schemaName = null,
        public readonly ?string $schemaDescription = null,
        public readonly ?float $temperature = null,
        public readonly ?int $maxTokens = null,
    ) {}

    public function toArray(): array
    {
        $result = [
            'model' => $this->model,
            'schema' => $this->schema,
        ];

        if ($this->prompt !== null) {
            $result['prompt'] = $this->prompt;
        }
        if ($this->messages !== null) {
            $result['messages'] = $this->messages;
        }
        if ($this->schemaName !== null) {
            $result['schemaName'] = $this->schemaName;
        }
        if ($this->schemaDescription !== null) {
            $result['schemaDescription'] = $this->schemaDescription;
        }
        if ($this->temperature !== null) {
            $result['temperature'] = $this->temperature;
        }
        if ($this->maxTokens !== null) {
            $result['maxTokens'] = $this->maxTokens;
        }

        return $result;
    }
}
