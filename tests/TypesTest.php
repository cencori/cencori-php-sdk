<?php

namespace Cencori\Tests;

use PHPUnit\Framework\TestCase;
use Cencori\Types\{Message, Usage, ChatResponse, EmbeddingUsage};

class TypesTest extends TestCase
{
    public function test_message_constructors(): void
    {
        $msg = Message::user('Hello');
        $this->assertEquals('user', $msg->role);
        $this->assertEquals('Hello', $msg->content);

        $msg = Message::assistant('Hi');
        $this->assertEquals('assistant', $msg->role);

        $msg = Message::system('Be helpful');
        $this->assertEquals('system', $msg->role);
    }

    public function test_chat_response_from_array(): void
    {
        $data = [
            'content' => 'Hello!',
            'model' => 'gpt-4o',
            'provider' => 'openai',
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 15,
                'total_tokens' => 25,
            ],
            'cost_usd' => 0.000125,
            'finish_reason' => 'stop',
        ];

        $response = ChatResponse::fromArray($data);
        $this->assertEquals('Hello!', $response->content);
        $this->assertEquals('gpt-4o', $response->model);
        $this->assertEquals(25, $response->usage->totalTokens);
    }

    public function test_usage_from_array(): void
    {
        $usage = Usage::fromArray([
            'prompt_tokens' => 10,
            'completion_tokens' => 20,
            'total_tokens' => 30,
        ]);
        $this->assertEquals(30, $usage->totalTokens);
    }

    public function test_embedding_usage_from_array(): void
    {
        $usage = EmbeddingUsage::fromArray(['total_tokens' => 5]);
        $this->assertEquals(5, $usage->totalTokens);
    }

    public function test_message_to_array(): void
    {
        $msg = Message::user('test');
        $arr = $msg->toArray();
        $this->assertEquals(['role' => 'user', 'content' => 'test'], $arr);
    }
}
