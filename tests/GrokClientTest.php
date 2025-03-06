<?php

namespace Oqzl\GrokClient\Tests;

use Oqzl\GrokClient\GrokClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class GrokClientTest extends TestCase
{
	/**
	 * Test that the client can be instantiated with method chaining
	 */
	public function testClientInstantiation()
	{
		$client = GrokClient::new()->apiKey('test-api-key');
		$this->assertInstanceOf(GrokClient::class, $client);
	}

	/**
	 * Test that the client properly sets configuration options
	 */
	public function testClientConfiguration()
	{
		$client = GrokClient::new()
			->apiKey('test-api-key')
			->model('grok-test-model')
			->baseUrl('https://test-api.example.com')
			->timeout(60)
			->temperature(0.8)
			->maxTokens(500);

		// Since these properties are private, we can't directly test them
		// But we're testing the method calls don't throw exceptions
		$this->assertInstanceOf(GrokClient::class, $client);
	}

	/**
	 * Test creating a chat completion with a mock response
	 */
	public function testCreateChatCompletion()
	{
		// Create a mock response
		$mockResponse = new Response(200, [], json_encode([
			'id' => 'chatcmpl-123',
			'object' => 'chat.completion',
			'created' => time(),
			'model' => 'grok-test-model',
			'choices' => [
				[
					'message' => [
						'role' => 'assistant',
						'content' => 'This is a test response'
					],
					'index' => 0,
					'finish_reason' => 'stop'
				]
			],
			'usage' => [
				'prompt_tokens' => 10,
				'completion_tokens' => 20,
				'total_tokens' => 30
			]
		]));

		// Set up the mock handler
		$mock = new MockHandler([$mockResponse]);
		$handlerStack = HandlerStack::create($mock);
		$httpClient = new Client(['handler' => $handlerStack]);

		// Create our client with the mocked HTTP client
		$client = GrokClient::new()
			->apiKey('test-api-key')
			->httpClient($httpClient);

		// Test chat completion
		$messages = [
			['role' => 'user', 'content' => 'This is a test message']
		];

		$response = $client->createChatCompletion($messages, 'grok-test-model');

		$this->assertEquals('This is a test response', $response['choices'][0]['message']['content']);
	}

	/**
	 * Test error handling with a mock error response
	 */
	public function testErrorHandling()
	{
		// Create a mock error response
		$mockResponse = new Response(400, [], json_encode([
			'error' => [
				'message' => 'Invalid API key',
				'type' => 'invalid_request_error',
				'code' => 'invalid_api_key'
			]
		]));

		// Set up the mock handler
		$mock = new MockHandler([$mockResponse]);
		$handlerStack = HandlerStack::create($mock);
		$httpClient = new Client(['handler' => $handlerStack]);

		// Create our client with the mocked HTTP client
		$client = GrokClient::new()
			->apiKey('invalid-api-key')
			->httpClient($httpClient);

		// Expect an exception
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Grok API error: Invalid API key');

		// This should trigger the exception
		$messages = [
			['role' => 'user', 'content' => 'This should fail']
		];

		$client->createChatCompletion($messages, 'grok-test-model');
	}
}
