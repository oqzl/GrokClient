<?php

namespace Oqzl\GrokClient\Tests;

use Oqzl\GrokClient\GrokClient;
use Oqzl\GrokClient\Chat;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ChatTest extends TestCase
{
	/**
	 * @var GrokClient
	 */
	private $client;

	/**
	 * @var array Mock response data
	 */
	private $mockResponseData;

	/**
	 * Set up test environment
	 */
	protected function setUp(): void
	{
		parent::setUp();

		// Set up response data
		$this->mockResponseData = [
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
		];

		// Create a mock response
		$mockResponse = new Response(200, [], json_encode($this->mockResponseData));

		// Set up the mock handler with multiple identical responses for multiple calls
		$mock = new MockHandler([
			$mockResponse,
			new Response(200, [], json_encode($this->mockResponseData)),
			new Response(200, [], json_encode($this->mockResponseData))
		]);

		$handlerStack = HandlerStack::create($mock);
		$httpClient = new Client(['handler' => $handlerStack]);

		// Create our client with the mocked HTTP client
		$this->client = GrokClient::new()
			->apiKey('test-api-key')
			->httpClient($httpClient);
	}

	/**
	 * Test creating a chat and getting a response
	 */
	public function testCreateChat()
	{
		$chat = $this->client->createChat('Test message');

		$this->assertInstanceOf(Chat::class, $chat);
		$this->assertEquals('This is a test response', $chat->response());
	}

	/**
	 * Test continuing a conversation
	 */
	public function testContinueConversation()
	{
		$chat = $this->client->createChat('Initial message');
		$this->assertEquals('This is a test response', $chat->response());

		// Continue the conversation
		$chat->reply('Follow-up message');
		$this->assertEquals('This is a test response', $chat->response());

		// Check that both messages are in the history
		$messages = $chat->messages();
		$this->assertCount(4, $messages); // System message + user + assistant + user + assistant

		// Check message content (assuming no system message)
		$this->assertEquals('user', $messages[0]['role']);
		$this->assertEquals('Initial message', $messages[0]['content']);

		$this->assertEquals('assistant', $messages[1]['role']);
		$this->assertEquals('This is a test response', $messages[1]['content']);

		$this->assertEquals('user', $messages[2]['role']);
		$this->assertEquals('Follow-up message', $messages[2]['content']);
	}

	/**
	 * Test getting different response formats
	 */
	public function testResponseFormats()
	{
		$chat = $this->client->createChat('Test message');

		// Test text response
		$this->assertEquals('This is a test response', $chat->response());

		// Test message object
		$message = $chat->responseMessage();
		$this->assertEquals('assistant', $message['role']);
		$this->assertEquals('This is a test response', $message['content']);

		// Test raw response
		$raw = $chat->rawResponse();
		$this->assertEquals($this->mockResponseData, $raw);
	}

	/**
	 * Test clearing chat history
	 */
	public function testClearHistory()
	{
		$chat = $this->client->createChat('Test message');

		// Verify we have messages
		$this->assertNotEmpty($chat->messages());

		// Clear history
		$chat->clear();

		// Verify chat history is cleared
		$this->assertEmpty($chat->messages());
	}
}
