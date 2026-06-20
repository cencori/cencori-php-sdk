#!/usr/bin/env php
<?php

/**
 * Full end-to-end demo of the Cencori PHP SDK.
 *
 * Run with: CENCORI_API_KEY=your-key php examples/demo.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Cencori\Cencori;
use Cencori\Types\{CreateProjectParams, CreateAPIKeyParams};

$apiKey = getenv('CENCORI_API_KEY');
if (!$apiKey) {
    echo "Please set CENCORI_API_KEY environment variable\n";
    exit(1);
}

$client = new Cencori(apiKey: $apiKey);
echo "🚀 Initialized Cencori client\n";

// 1. Chat Completion
echo "\n--- Chat Completion ---\n";
$response = $client->ai->chat(
    messages: [['role' => 'user', 'content' => "Hello! say 'Cencori is awesome'"]],
    model: 'gpt-4o',
);
echo "Response: {$response->content}\n";

// 2. Embeddings
echo "\n--- Embeddings ---\n";
$embedding = $client->ai->embeddings(
    input: 'Cencori AI Infrastructure',
    model: 'text-embedding-3-small',
);
echo 'Embedding generated (dim: ' . count($embedding->embeddings[0]) . ")\n";

// 3. Project Management
echo "\n--- Project Management ---\n";
try {
    $project = $client->projects->create(
        orgSlug: 'test-org',
        params: new CreateProjectParams(name: 'Demo Project', visibility: 'private'),
    );
    echo "Created project: {$project->name} ({$project->id})\n";

    // 4. API Keys
    echo "\n--- API Keys ---\n";
    $key = $client->apiKeys->create(
        projectId: $project->id,
        params: new CreateAPIKeyParams(name: 'Demo Key', environment: 'test'),
    );
    echo "Created API Key: {$key->prefix}...\n";

    // 5. Metrics
    echo "\n--- Metrics ---\n";
    $metrics = $client->metrics->get(period: '24h');
    echo "Total Requests (24h): {$metrics->requests->total}\n";

    // Cleanup
    echo "\n--- Cleanup ---\n";
    $client->projects->delete('test-org', $project->slug);
    echo "Deleted project\n";
} catch (\Exception $e) {
    echo "⚠️ Skipped Admin/Management steps: {$e->getMessage()}\n";
}
