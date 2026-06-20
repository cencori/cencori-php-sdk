# Cencori PHP SDK

Official PHP SDK for Cencori - AI Infrastructure for Production.

One SDK for AI Gateway, Compute, Workflow, and Storage.
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

$cencori = new Cencori(apiKey: 'your-api-key');

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

## Streaming

```php
foreach ($cencori->ai->chatStream(
    messages: [['role' => 'user', 'content' => 'Tell me a story']],
    model: 'gpt-4o',
) as $chunk) {
    echo $chunk->delta;
}
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
| OpenAI | `gpt-4o`, `gpt-4-turbo`, `gpt-3.5-turbo` |
| Anthropic | `claude-3-opus`, `claude-3-sonnet`, `claude-3-haiku` |
| Google | `gemini-2.5-flash`, `gemini-2.0-flash` |

## License

MIT © FohnAI
