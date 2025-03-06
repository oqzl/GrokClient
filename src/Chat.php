<?php

namespace Oqzl\GrokClient;

/**
 * Chat - Class for managing chat interactions with Grok API
 */
class Chat
{
	/**
	 * @var GrokClient The Grok client instance
	 */
	private $client;

	/**
	 * @var array Message history for the chat
	 */
	private $messages = [];

	/**
	 * @var string The model to use for this chat
	 */
	private $model;

	/**
	 * @var array Options for API calls
	 */
	private $options;

	/**
	 * @var array|null The last response received from the API
	 */
	private $lastResponse = null;

	/**
	 * Constructor
	 * 
	 * @param GrokClient $client The Grok client instance
	 * @param string $model The model to use
	 * @param string $systemPrompt Optional system message to start the chat
	 * @param array $options Additional options for API calls
	 */
	public function __construct(GrokClient $client, string $model, string $systemPrompt = '', array $options = [])
	{
		$this->client = $client;
		$this->model = $model;
		$this->options = $options;

		// Add system message if provided
		if (!empty($systemPrompt)) {
			$this->messages[] = [
				'role' => 'system',
				'content' => $systemPrompt
			];
		}
	}

	/**
	 * Add a user prompt to the chat and get a response
	 * 
	 * @param string $prompt User prompt
	 * @return $this For method chaining
	 */
	public function prompt(string $prompt): self
	{
		$this->messages[] = [
			'role' => 'user',
			'content' => $prompt
		];

		$this->lastResponse = $this->client->createChatCompletion(
			$this->messages,
			$this->model,
			$this->options
		);

		// Add assistant's response to the chat history
		if (isset($this->lastResponse['choices'][0]['message']['content'])) {
			$this->messages[] = [
				'role' => 'assistant',
				'content' => $this->lastResponse['choices'][0]['message']['content']
			];
		}

		return $this;
	}

	/**
	 * Send a follow-up message (alias for prompt)
	 * 
	 * @param string $prompt Follow-up user prompt
	 * @return $this For method chaining
	 */
	public function reply(string $prompt): self
	{
		return $this->prompt($prompt);
	}

	/**
	 * Get the formatted response text from the last API call
	 * 
	 * @return string The response text
	 */
	public function response(): string
	{
		if ($this->lastResponse === null) {
			return '';
		}

		return $this->lastResponse['choices'][0]['message']['content'] ?? '';
	}

	/**
	 * Get the raw response from the last API call
	 * 
	 * @return array|null The raw response data
	 */
	public function rawResponse(): ?array
	{
		return $this->lastResponse;
	}

	/**
	 * Get the raw response message object from the last API call
	 * 
	 * @return array|null The response message object
	 */
	public function responseMessage(): ?array
	{
		if ($this->lastResponse === null) {
			return null;
		}

		return $this->lastResponse['choices'][0]['message'] ?? null;
	}

	/**
	 * Get all messages in the chat history
	 * 
	 * @return array The chat history
	 */
	public function messages(): array
	{
		return $this->messages;
	}

	/**
	 * Clear the chat history
	 * 
	 * @return $this For method chaining
	 */
	public function clear(): self
	{
		// Keep only the system message if it exists
		if (!empty($this->messages) && $this->messages[0]['role'] === 'system') {
			$this->messages = [$this->messages[0]];
		} else {
			$this->messages = [];
		}

		$this->lastResponse = null;
		return $this;
	}

	/**
	 * Set an option for subsequent API calls
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
	 * Set multiple options for subsequent API calls
	 * 
	 * @param array $options Options to set
	 * @return $this For method chaining
	 */
	public function options(array $options): self
	{
		$this->options = array_merge($this->options, $options);
		return $this;
	}

	/**
	 * Set the model for subsequent API calls
	 * 
	 * @param string $model Model name
	 * @return $this For method chaining
	 */
	public function model(string $model): self
	{
		$this->model = $model;
		return $this;
	}
}
