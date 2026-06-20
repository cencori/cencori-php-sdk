#!/usr/bin/env php
<?php

/**
 * Basic usage examples for the Cencori PHP SDK.
 *
 * Run with: php examples/basic_usage.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Cencori\{Cencori, CencoriError};
use Cencori\Errors\{AuthenticationError, RateLimitError, SafetyError};

$cencori = new Cencori(apiKey: 'your-api-key');

// Example 1: Basic chat
echo "=== Basic Chat ===\n";
$response = $cencori->ai->chat(
    messages: [['role' => 'user', 'content' => 'What is the capital of France?']],
    model: 'gpt-4o',
);
echo "Response: {$response->content}\n";
echo "Model: {$response->model}\n";
echo "Cost: \${$response->costUsd}\n";
echo "Tokens: {$response->usage->totalTokens}\n";

// Example 2: Streaming
echo "\n=== Streaming ===\n";
echo "Streaming response: ";
foreach ($cencori->ai->chatStream(
    messages: [['role' => 'user', 'content' => 'Tell me a short story about a robot.']],
    model: 'gpt-4o',
) as $chunk) {
    echo $chunk->delta;
}
echo "\n";

// Example 3: Error handling
echo "\n=== Error Handling ===\n";
try {
    $response = $cencori->ai->chat(
        messages: [['role' => 'user', 'content' => 'Hello!']],
    );
    echo "Response: {$response->content}\n";
} catch (AuthenticationError $e) {
    echo "Error: Invalid API key\n";
} catch (RateLimitError $e) {
    echo "Error: Rate limit exceeded\n";
} catch (SafetyError $e) {
    echo "Error: Content blocked - " . implode(', ', $e->getReasons()) . "\n";
} catch (CencoriError $e) {
    echo "Error: {$e->getMessage()}\n";
}

// Example 4: Multi-turn conversation
echo "\n=== Conversation ===\n";
$messages = [
    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
    ['role' => 'user', 'content' => 'My name is Alice.'],
];

$response1 = $cencori->ai->chat(messages: $messages, model: 'gpt-4o');
echo "Assistant: {$response1->content}\n";

$messages[] = ['role' => 'assistant', 'content' => $response1->content];
$messages[] = ['role' => 'user', 'content' => "What's my name?"];

$response2 = $cencori->ai->chat(messages: $messages, model: 'gpt-4o');
echo "Assistant: {$response2->content}\n";
