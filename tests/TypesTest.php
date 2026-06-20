<?php

namespace Cencori\Tests;

use PHPUnit\Framework\TestCase;
use Cencori\Types\{
    Message,
    Usage,
    ChatResponse,
    EmbeddingUsage,
    EmbeddingResponse,
    StreamChunk,
    ToolDefinition,
    ToolCall,
    ToolChoice,
    GeneratedImage,
    ImageGenerationResponse,
    RagResponse,
    RagStreamChunk,
    Agent,
    AgentConfig,
    AgentListItem,
    AgentKey,
    CreateAgentParams,
    UpdateAgentParams,
    CreateAgentKeyParams,
    MemoryNamespace,
    Memory,
    CreateNamespaceOptions,
    StoreMemoryOptions,
    SearchMemoryOptions,
    SearchResult,
    WebTelemetryPayload,
    GenerateObjectResponse,
};

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

    public function test_stream_chunk_with_tool_calls(): void
    {
        $chunk = StreamChunk::fromArray([
            'delta' => '',
            'finish_reason' => 'tool_calls',
            'toolCalls' => [['id' => 'call_1', 'type' => 'function', 'function' => ['name' => 'test', 'arguments' => '{}']]],
        ]);
        $this->assertEquals('tool_calls', $chunk->finishReason);
        $this->assertNotNull($chunk->toolCalls);
        $this->assertCount(1, $chunk->toolCalls);
    }

    // ── Tool Types ──

    public function test_tool_definition(): void
    {
        $tool = new ToolDefinition('get_weather', 'Get current weather', [
            'type' => 'object',
            'properties' => ['city' => ['type' => 'string']],
        ]);
        $arr = $tool->toArray();
        $this->assertEquals('function', $arr['type']);
        $this->assertEquals('get_weather', $arr['function']['name']);
        $this->assertEquals('Get current weather', $arr['function']['description']);
    }

    public function test_tool_call(): void
    {
        $tc = ToolCall::fromArray([
            'id' => 'call_1',
            'type' => 'function',
            'function' => ['name' => 'get_weather', 'arguments' => '{"city":"London"}'],
        ]);
        $this->assertEquals('call_1', $tc->id);
        $this->assertEquals('get_weather', $tc->name);
        $this->assertEquals('{"city":"London"}', $tc->arguments);
    }

    public function test_tool_choice(): void
    {
        $this->assertEquals('auto', ToolChoice::auto()->toValue());
        $this->assertEquals('none', ToolChoice::none()->toValue());
        $this->assertEquals('required', ToolChoice::required()->toValue());

        $func = ToolChoice::function('get_weather')->toValue();
        $this->assertIsArray($func);
        $this->assertEquals('function', $func['type']);
    }

    // ── Generated Image Types ──

    public function test_generated_image(): void
    {
        $img = GeneratedImage::fromArray([
            'url' => 'https://example.com/image.png',
            'b64_json' => null,
            'revisedPrompt' => 'A revised prompt',
        ]);
        $this->assertEquals('https://example.com/image.png', $img->url);
        $this->assertEquals('A revised prompt', $img->revisedPrompt);
    }

    public function test_image_generation_response(): void
    {
        $resp = ImageGenerationResponse::fromArray([
            'images' => [
                ['url' => 'https://example.com/1.png'],
                ['url' => 'https://example.com/2.png'],
            ],
            'model' => 'dall-e-3',
            'provider' => 'openai',
        ]);
        $this->assertCount(2, $resp->images);
        $this->assertEquals('dall-e-3', $resp->model);
    }

    // ── RAG Types ──

    public function test_rag_response(): void
    {
        $resp = RagResponse::fromArray([
            'message' => ['role' => 'assistant', 'content' => 'Here is the info'],
            'model' => 'gpt-4o',
            'provider' => 'openai',
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 20, 'total_tokens' => 30],
            'latency_ms' => 150,
            'sources' => [
                ['content' => 'Source doc', 'metadata' => ['title' => 'Doc 1'], 'similarity' => 0.95],
            ],
        ]);
        $this->assertEquals('Here is the info', $resp->message['content']);
        $this->assertCount(1, $resp->sources);
        $this->assertEquals(150, $resp->latencyMs);
    }

    public function test_rag_stream_chunk(): void
    {
        $chunk = RagStreamChunk::fromArray([
            'type' => 'content',
            'delta' => 'Hello',
            'finish_reason' => null,
        ]);
        $this->assertEquals('content', $chunk->type);
        $this->assertEquals('Hello', $chunk->delta);
    }

    // ── Agent Types ──

    public function test_agent_config_from_array(): void
    {
        $config = AgentConfig::fromArray([
            'model' => 'gpt-4o',
            'system_prompt' => 'Be helpful',
            'tools' => ['web_search'],
            'temperature' => 0.7,
        ]);
        $this->assertEquals('gpt-4o', $config->model);
        $this->assertEquals('Be helpful', $config->systemPrompt);
        $this->assertEquals(['web_search'], $config->tools);
    }

    public function test_agent_from_array(): void
    {
        $agent = Agent::fromArray([
            'id' => 'ag_123',
            'name' => 'My Agent',
            'description' => 'A test agent',
            'is_active' => true,
            'shadow_mode' => false,
            'created_at' => '2024-01-01T00:00:00Z',
            'config' => ['model' => 'gpt-4o', 'system_prompt' => null, 'tools' => [], 'temperature' => null],
        ]);
        $this->assertEquals('ag_123', $agent->id);
        $this->assertEquals('My Agent', $agent->name);
        $this->assertTrue($agent->isActive);
        $this->assertNotNull($agent->config);
    }

    public function test_agent_list_item(): void
    {
        $item = AgentListItem::fromArray([
            'id' => 'ag_123',
            'name' => 'My Agent',
            'is_active' => true,
            'shadow_mode' => false,
            'created_at' => '2024-01-01T00:00:00Z',
        ]);
        $this->assertEquals('ag_123', $item->id);
        $this->assertTrue($item->isActive);
    }

    public function test_create_agent_params(): void
    {
        $params = new CreateAgentParams(
            name: 'Test Agent',
            description: 'A test',
            config: new AgentConfig('gpt-4o', 'Be helpful'),
        );
        $arr = $params->toArray();
        $this->assertEquals('Test Agent', $arr['name']);
        $this->assertEquals('gpt-4o', $arr['config']['model']);
    }

    public function test_update_agent_params(): void
    {
        $params = new UpdateAgentParams(name: 'Renamed');
        $arr = $params->toArray();
        $this->assertEquals('Renamed', $arr['name']);
        $this->assertArrayNotHasKey('is_active', $arr);
    }

    public function test_agent_key_from_array(): void
    {
        $key = AgentKey::fromArray([
            'id' => 'key_123',
            'name' => 'Prod Key',
            'key_prefix' => 'csk_prod_',
            'environment' => 'production',
            'key_type' => 'secret',
            'agent_id' => 'ag_123',
            'created_at' => '2024-01-01T00:00:00Z',
            'full_key' => 'csk_prod_abc123',
        ]);
        $this->assertEquals('key_123', $key->id);
        $this->assertEquals('csk_prod_abc123', $key->fullKey);
    }

    public function test_create_agent_key_params(): void
    {
        $params = new CreateAgentKeyParams(
            name: 'My Key',
            environment: 'production',
            keyType: 'secret',
        );
        $arr = $params->toArray();
        $this->assertEquals('production', $arr['environment']);
        $this->assertArrayNotHasKey('allowed_domains', $arr);
    }

    // ── Memory Types ──

    public function test_memory_namespace_from_array(): void
    {
        $ns = MemoryNamespace::fromArray([
            'id' => 'ns_123',
            'name' => 'conversations',
            'embeddingModel' => 'text-embedding-3-small',
            'dimensions' => 1536,
            'created_at' => '2024-01-01T00:00:00Z',
            'description' => 'Chat history',
        ]);
        $this->assertEquals('ns_123', $ns->id);
        $this->assertEquals(1536, $ns->dimensions);
    }

    public function test_memory_from_array(): void
    {
        $mem = Memory::fromArray([
            'id' => 'mem_123',
            'namespace' => 'conversations',
            'content' => 'Hello world',
            'created_at' => '2024-01-01T00:00:00Z',
            'metadata' => ['key' => 'value'],
        ]);
        $this->assertEquals('Hello world', $mem->content);
        $this->assertEquals(['key' => 'value'], $mem->metadata);
    }

    public function test_create_namespace_options(): void
    {
        $opts = new CreateNamespaceOptions('my-ns', 'My namespace', 'text-embedding-3-small', 1536);
        $arr = $opts->toArray();
        $this->assertEquals('my-ns', $arr['name']);
        $this->assertEquals(1536, $arr['dimensions']);
    }

    public function test_store_memory_options(): void
    {
        $opts = new StoreMemoryOptions('my-ns', 'Test content', metadata: ['key' => 'val']);
        $arr = $opts->toArray();
        $this->assertEquals('my-ns', $arr['namespace']);
        $this->assertEquals('Test content', $arr['content']);
    }

    public function test_search_memory_options(): void
    {
        $opts = new SearchMemoryOptions('my-ns', 'test query', limit: 10, threshold: 0.7);
        $arr = $opts->toArray();
        $this->assertEquals('test query', $arr['query']);
        $this->assertEquals(10, $arr['limit']);
    }

    public function test_search_result_from_array(): void
    {
        $result = SearchResult::fromArray([
            'results' => [
                ['id' => 'mem_1', 'namespace' => 'ns', 'content' => 'Result 1', 'created_at' => 'now', 'metadata' => []],
                ['id' => 'mem_2', 'namespace' => 'ns', 'content' => 'Result 2', 'created_at' => 'now', 'metadata' => []],
            ],
            'query' => 'test',
            'namespace' => 'ns',
            'count' => 2,
            'latency_ms' => 50,
        ]);
        $this->assertCount(2, $result->results);
        $this->assertEquals(50, $result->latencyMs);
    }

    // ── Telemetry Type ──

    public function test_web_telemetry_payload(): void
    {
        $payload = new WebTelemetryPayload(
            host: 'example.com',
            method: 'GET',
            path: '/api/chat',
            statusCode: 200,
            userAgent: 'Mozilla/5.0',
            latencyMs: 100,
        );
        $arr = $payload->toArray();
        $this->assertEquals('example.com', $arr['host']);
        $this->assertEquals(200, $arr['statusCode']);
        $this->assertEquals(100, $arr['latencyMs']);
        $this->assertArrayNotHasKey('requestId', $arr);
    }

    // ── GenerateObjectResponse ──

    public function test_generate_object_response(): void
    {
        $resp = GenerateObjectResponse::fromArray([
            'object' => ['name' => 'John', 'age' => 30],
            'usage' => ['promptTokens' => 10, 'completionTokens' => 20, 'totalTokens' => 30],
        ]);
        $this->assertEquals(['name' => 'John', 'age' => 30], $resp->object);
        $this->assertEquals(30, $resp->totalTokens);
    }
}
