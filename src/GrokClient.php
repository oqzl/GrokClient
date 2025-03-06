<?php

namespace Oqzl\GrokClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

/**
 * GrokClient - PHP client for interacting with xAI's Grok API
 */
class GrokClient
{
	/**
	 * @var string API key for authentication
	 */
	private $apiKey;

	/**
	 * @var string Base URL for the Grok API
	 */
	private $baseUrl = 'https://api.x.ai/v1';

	/**
	 * @var int Timeout for API requests in seconds
	 */
	private $timeout = 30;

	/**
	 * @var string The model to use
	 */
	private $model = 'grok-1';

	/**
	 * @var string System prompt to use for conversations
	 */
	private $systemPrompt = '';

	/**
	 * @var array Additional options for API calls
	 */
	private $options = [];

	/**
	 * @var Client HTTP client
	 */
	private $httpClient;

	/**
	 * Static factory method
	 * 
	 * @return self New GrokClient instance
	 */
	public static function new(): self
	{
		return new self();
	}

	/**
	 * Set the API key
	 * 
	 * @param string $apiKey The API key
	 * @return $this For method chaining
	 */
	public function apiKey(string $apiKey): self
	{
		$this->apiKey = $apiKey;
		return $this;
	}

	/**
	 * Set the model
	 * 
	 * @param string $model The model name
	 * @return $this For method chaining
	 */
	public function model(string $model): self
	{
		$this->model = $model;
		return $this;
	}

	/**
	 * Set the system prompt
	 * 
	 * @param string $systemPrompt The system prompt
	 * @return $this For method chaining
	 */
	public function systemPrompt(string $systemPrompt): self
	{
		$this->systemPrompt = $systemPrompt;
		return $this;
	}

	/**
	 * Set the base URL
	 * 
	 * @param string $baseUrl The base URL
	 * @return $this For method chaining
	 */
	public function baseUrl(string $baseUrl): self
	{
		$this->baseUrl = $baseUrl;
		return $this;
	}

	/**
	 * Set the timeout
	 * 
	 * @param int $timeout Timeout in seconds
	 * @return $this For method chaining
	 */
	public function timeout(int $timeout): self
	{
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * Set additional options
	 * 
	 * @param array $options Additional options for API calls
	 * @return $this For method chaining
	 */
	public function options(array $options): self
	{
		$this->options = $options;
		return $this;
	}

	/**
	 * Set a single option
	 * 
	 * @param string $key Option key
	 * @param mixed $value Option value
	 * @return $this For method chaining
	 */
	public function option(string $key, $value): self
	{
		$this->options[$key] = $value;
		return $this;
	}

	/**
	 * Set the temperature
	 * 
	 * @param float $temperature Temperature value (0.0 to 1.0)
	 * @return $this For method chaining
	 */
	public function temperature(float $temperature): self
	{
		return $this->option('temperature', $temperature);
	}

	/**
	 * Set the max tokens
	 * 
	 * @param int $maxTokens Maximum number of tokens to generate
	 * @return $this For method chaining
	 */
	public function maxTokens(int $maxTokens): self
	{
		return $this->option('max_tokens', $maxTokens);
	}

	/**
	 * Set HTTP client
	 * 
	 * @param Client $client Guzzle HTTP client
	 * @return $this For method chaining
	 */
	public function httpClient(Client $client): self
	{
		$this->httpClient = $client;
		return $this;
	}

	/**
	 * Get or create HTTP client
	 * 
	 * @return Client The HTTP client
	 */
	private function getHttpClient(): Client
	{
		if (!$this->httpClient) {
			$this->httpClient = new Client([
				// 'base_uri' => $this->baseUrl,
				'timeout' => $this->timeout,
				'headers' => [
					'Authorization' => 'Bearer ' . $this->apiKey,
					'Content-Type' => 'application/json',
					'Accept' => 'application/json',
				]
			]);
		}

		return $this->httpClient;
	}

	/**
	 * Create a new chat with an initial prompt
	 * 
	 * @param string $prompt Initial user prompt
	 * @return Chat A new chat instance
	 * @throws Exception When API key is not set
	 */
	public function createChat(string $prompt): Chat
	{
		if (empty($this->apiKey)) {
			throw new Exception('API key must be set before creating a chat');
		}

		$chat = new Chat($this, $this->model, $this->systemPrompt, $this->options);
		return $chat->prompt($prompt);
	}

	/**
	 * Send a chat completion request to Grok
	 * 
	 * @param array $messages Array of message objects with role and content
	 * @param string $model Model to use
	 * @param array $options Additional parameters
	 * @return array Response from the API
	 * @throws Exception When API request fails
	 */
	public function createChatCompletion(array $messages, string $model, array $options = []): array
	{
		$data = array_merge([
			'model' => $model,
			'messages' => $messages,
		], $options);

		return $this->request('POST', '/chat/completions', $data);
	}

	/**
	 * Get information about available models
	 * 
	 * @return array Array of model information
	 * @throws Exception When API request fails
	 */
	public function listModels(): array
	{
		return $this->request('GET', '/models');
	}

	/**
	 * Get information about a specific model
	 * 
	 * @param string $modelId ID of the model
	 * @return array Model information
	 * @throws Exception When API request fails
	 */
	public function getModel(string $modelId): array
	{
		return $this->request('GET', '/models/' . $modelId);
	}

	/**
	 * Make a request to the Grok API
	 * 
	 * @param string $method HTTP method (GET, POST, etc.)
	 * @param string $endpoint API endpoint
	 * @param array $data Request data
	 * @return array Response data
	 * @throws Exception When request fails
	 */
	private function request(string $method, string $endpoint, array $data = []): array
	{
		$client = $this->getHttpClient();

		$options = [];
		if (!empty($data)) {
			$options['json'] = $data;
		}

		try {
			// var_dump([$method, $this->baseUrl . $endpoint, $options]);
			$response = $client->request($method, $this->baseUrl . $endpoint, $options);
			$body = $response->getBody()->getContents();
			return json_decode($body, true);
		} catch (GuzzleException $e) {
			// Handle Guzzle specific exceptions
			if (method_exists($e, 'hasResponse') && $e->hasResponse()) {
				$response = $e->getResponse();
				$body = $response->getBody()->getContents();
				$errorData = json_decode($body, true);

				$errorMessage = isset($errorData['error']['message'])
					? $errorData['error']['message']
					: 'Unknown API error';

				throw new Exception('Grok API error: ' . $errorMessage, $response->getStatusCode());
			}

			throw new Exception('HTTP request failed: ' . $e->getMessage(), 0, $e);
		}
	}
}
