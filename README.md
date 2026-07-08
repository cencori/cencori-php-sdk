# Cencori PHP SDK v1.3.0

Official PHP SDK for Cencori - AI Infrastructure for Production.

One SDK for AI Gateway, Agents, Memory, Compute, Workflow, and Storage.
Every operation is secured, logged, and tracked.

## Installation

Install via Composer:

```bash
composer require cencori/cencori-php
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Cencori\Cencori;

$cencori = new Cencori(['apiKey' => 'your-api-key']);

// Chat
$response = $cencori->ai->chat(
    messages: [['role' => 'user', 'content' => 'Hello!']],
);
echo $response->content;

// Embeddings
$embedding = $cencori->ai->embeddings(
    input: 'Hello world',
    model: 'text-embedding-3-small',
);
echo count($embedding->embeddings[0]);
```

## Configuration

The constructor accepts a config array:

```php
$cencori = new Cencori([
    'apiKey' => 'csk_...',                        // Falls back to CENCORI_API_KEY env var
    'baseUrl' => 'https://api.cencori.com',       // Default
    'headers' => ['X-Custom' => 'value'],          // Custom headers
    'timeout' => 30.0,                             // Request timeout in seconds
    'maxRetries' => 3,                             // Retries on 5xx with exponential backoff
]);
```

## AI Module

### Chat

```php
$response = $cencori->ai->chat(
    messages: [['role' => 'user', 'content' => 'Tell me a story']],
    model: 'gpt-4o',
    temperature: 0.7,
    maxTokens: 1000,
);
echo $response->content;
```

### Streaming

```php
foreach ($cencori->ai->chatStream(
    messages: [['role' => 'user', 'content' => 'Tell me a story']],
    model: 'gpt-4o',
) as $chunk) {
    echo $chunk->delta;
}
```

### Structured Output (JSON Schema)

```php
$result = $cencori->ai->generateObject(
    model: 'gpt-4o',
    prompt: 'Generate a user profile',
    schema: [
        'type' => 'object',
        'properties' => [
            'name' => ['type' => 'string'],
            'age' => ['type' => 'number'],
        ],
        'required' => ['name', 'age'],
    ],
);
print_r($result['object']); // ['name' => 'John', 'age' => 30]
```

### Vision (Image Understanding)

Analyze, describe, OCR, and classify images. Routes across OpenAI, Anthropic, and Google.

```php
// General analysis with a custom prompt
$result = $cencori->vision->analyze([
    'image_url' => 'https://example.com/photo.jpg',
    'prompt' => 'What breed of dog is this?',
]);
echo $result['analysis'];

// OCR from a local file
$b64 = base64_encode(file_get_contents('receipt.png'));
$ocr = $cencori->vision->ocr(['image_base64' => $b64, 'mime_type' => 'image/png']);
echo $ocr['text'];

// Structured classification
$c = $cencori->vision->classify(['image_url' => 'https://example.com/product.jpg']);
// $c['classification'] is an array when the model returns valid JSON
```

Full API in [docs](https://cencori.com/docs/ai/endpoints/vision).

### Documents (PDF & Image Extraction)

Extract text from PDFs and images, summarize, and answer questions. Text-based PDFs use native parsing — no LLM tokens.

```php
// Extract text (native PDF parse — free)
$result = $cencori->documents->extract([
    'document_url' => 'https://example.com/contract.pdf',
]);
echo $result['method']; // 'pdf_text' — no LLM cost

// Summarize
$summary = $cencori->documents->summarize([
    'document_url' => 'https://example.com/report.pdf',
]);
echo $summary['summary'];

// Q&A — strict "Not found" if answer isn't present
$answer = $cencori->documents->query([
    'document_url' => 'https://example.com/contract.pdf',
    'question' => 'What is the termination clause?',
]);
echo $answer['answer'];
```

Full API in [docs](https://cencori.com/docs/ai/endpoints/documents).

### Image Generation

```php
$result = $cencori->ai->generateImage(
    prompt: 'A futuristic city at sunset',
    model: 'dall-e-3',
    size: '1024x1024',
);
echo $result['images'][0]['url'];
```

### RAG (Retrieval-Augmented Generation)

```php
$response = $cencori->ai->rag(
    model: 'gpt-4o',
    messages: [['role' => 'user', 'content' => 'What are our policies?']],
    namespace: 'company-docs',
    limit: 5,
);
echo $response['message']['content'];
print_r($response['sources']);
```

### Streaming RAG

```php
foreach ($cencori->ai->ragStream(
    model: 'gpt-4o',
    messages: [['role' => 'user', 'content' => 'What are our policies?']],
    namespace: 'company-docs',
) as $chunk) {
    if ($chunk['type'] === 'content') {
        echo $chunk['delta'];
    }
}
```

### Responses API (OpenAI-compatible)

```php
$response = $cencori->ai->responses(
    model: 'gpt-4o',
    input: 'What is the weather in San Francisco?',
    tools: [['type' => 'web_search_preview']],
);
print_r($response);
```

### Streaming Responses API

```php
foreach ($cencori->ai->responsesStream(
    model: 'gpt-4o',
    input: 'Tell me about AI',
) as $event) {
    if ($event['type'] === 'response.output_text.delta') {
        echo $event['data']['delta'];
    }
}
```

## Agents Module

Create and manage AI agents programmatically.

```php
// Create an agent
$agent = $cencori->agents->create([
    'name' => 'my-agent',
    'config' => [
        'model' => 'gpt-4o',
        'system_prompt' => 'You are a helpful assistant.',
    ],
]);

// List agents
$agents = $cencori->agents->list();

// Get a specific agent
$agent = $cencori->agents->get('agent_123');

// Update agent configuration
$agent = $cencori->agents->updateConfig('agent_123', [
    'name' => 'updated-agent',
    'config' => ['temperature' => 0.5],
]);

// Create an agent API key
$key = $cencori->agents->createKey('agent_123', [
    'name' => 'prod-key',
    'environment' => 'production',
]);

// Delete an agent
$cencori->agents->delete('agent_123');
```

## Memory Module

Vector storage for RAG, conversation history, and semantic search.

```php
// Create a namespace
$namespace = $cencori->memory->createNamespace([
    'name' => 'conversations',
]);

// List namespaces
$namespaces = $cencori->memory->listNamespaces();

// Store a memory
$memory = $cencori->memory->store([
    'namespace' => 'conversations',
    'content' => 'User asked about pricing plans',
    'metadata' => ['userId' => 'user_123'],
]);

// Semantic search
$results = $cencori->memory->search([
    'namespace' => 'conversations',
    'query' => 'what did we discuss about pricing?',
    'limit' => 5,
]);

// Get a memory by ID
$memory = $cencori->memory->get('mem_123');

// Delete a memory
$result = $cencori->memory->delete('mem_123');

// Batch store
$memories = $cencori->memory->storeBatch('conversations', [
    ['content' => 'Memory 1', 'metadata' => ['key' => 'value1']],
    ['content' => 'Memory 2', 'metadata' => ['key' => 'value2']],
]);

// Delete by filter
$result = $cencori->memory->deleteByFilter('conversations', ['userId' => 'user_123']);
```

## Sessions Module

Durable execution sessions for AI agents — pause/resume execution, event sourcing, and human-in-the-loop approval workflows.

```php
// Create a session
$session = $cencori->sessions->create([
    'agent_id' => 'ag_...',
]);

// List sessions
$sessions = $cencori->sessions->list(['status' => 'active']);

// Get a session by ID
$session = $cencori->sessions->get('sess_123');

// Submit a turn (returns SSE stream)
$response = $cencori->sessions->submitTurn('sess_123', [
    'input' => 'What is the weather in San Francisco?',
    'pause_on_tool_calls' => true,
]);
$body = $response->getBody();
while (!$body->eof()) {
    echo $body->read(1024);
}

// Get session events
$events = $cencori->sessions->getEvents('sess_123', [
    'turn_number' => 1,
]);

// Approve a pending action (returns SSE stream)
$response = $cencori->sessions->approve('sess_123', [
    'action_id' => 'act_...',
    'tool_results' => [
        ['action_id' => 'act_...', 'output' => '{"temperature": 72}'],
    ],
]);

// Reject a pending action
$result = $cencori->sessions->reject('sess_123', [
    'action_id' => 'act_...',
]);
echo $result['resolution']; // "rejected"

// Delete a session
$cencori->sessions->delete('sess_123');
```

## Telemetry Module

Report web traffic from your application to the Cencori dashboard.

```php
$cencori->telemetry->reportWebRequest([
    'host' => 'myapp.example.com',
    'method' => 'GET',
    'path' => '/api/chat',
    'statusCode' => 200,
    'userAgent' => 'Mozilla/5.0',
    'latencyMs' => 150,
]);
```

## Project Management

```php
use Cencori\Types\CreateProjectParams;

// List projects
$projects = $cencori->projects->list(orgSlug: 'my-org');

// Create project
$project = $cencori->projects->create(
    orgSlug: 'my-org',
    params: new CreateProjectParams(name: 'New Project', visibility: 'private'),
);
```

## API Key Management

```php
use Cencori\Types\CreateAPIKeyParams;

// Create API key
$key = $cencori->apiKeys->create(
    projectId: 'proj_123',
    params: new CreateAPIKeyParams(name: 'Dev Key', environment: 'dev'),
);
echo "Secret Key: {$key->key}\n"; // Only shown once!

// Get key stats
$stats = $cencori->apiKeys->getStats(projectId: 'proj_123', keyId: $key->id);
```

## Metrics & Analytics

```php
// Get usage metrics for last 24 hours
$metrics = $cencori->metrics->get(period: '24h');

echo "Total Requests: {$metrics->requests->total}\n";
echo "Total Cost: \${$metrics->cost->totalUsd}\n";
```

## Error Handling

```php
use Cencori\Cencori;
use Cencori\Errors\{AuthenticationError, RateLimitError, SafetyError};

try {
    $response = $cencori->ai->chat(messages: [...]);
} catch (AuthenticationError $e) {
    echo "Invalid API key";
} catch (RateLimitError $e) {
    echo "Too many requests";
} catch (SafetyError $e) {
    echo "Content blocked: " . implode(', ', $e->getReasons());
}
```

## Supported Models

| Provider | Models |
|----------|--------|
| OpenAI | `gpt-4o`, `gpt-4-turbo`, `gpt-3.5-turbo`, `dall-e-3`, `dall-e-2`, `gpt-image-1.5` |
| Anthropic | `claude-3-opus`, `claude-3-sonnet`, `claude-3-haiku` |
| Google | `gemini-2.5-flash`, `gemini-2.0-flash`, `gemini-3-pro-image`, `imagen-3` |

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for development setup, workflow, and guidelines.

## License

MIT © FohnAI
