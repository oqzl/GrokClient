## Usage

```php
<?php

require 'vendor/autoload.php';

use Oqzl\GrokClient\GrokClient;

// Initialize the client
$grok = GrokClient::new()
    ->apiKey('your-api-key')
    ->model('grok-1')
    ->systemPrompt('You are a helpful AI assistant.');

// Start a chat
$chat = $grok->createChat('Hello, Grok!');
echo $chat->response();

// Continue the conversation
$chat->reply('Tell me about quantum computing');
echo $chat->response();

// Different response formats
$messageObj = $chat->responseMessage(); // Message object
$fullResponse = $chat->rawResponse();   // Complete API response
```

## Requirements

- PHP 7.2 or later
- Guzzle HTTP client 7.0 or later

## License

MIT

## Documentation

See [README_ja.md](README_ja.md) for Japanese documentation.# Grok Client

A modern PHP client for interacting with xAI's Grok API.

## Features

- Fluent interface with method chaining
- Simple chat-based interactions
- Multiple response formats
- Composer compatible
- PSR-4 autoloading

## Installation

This library is not registered on Packagist. You can install it using the following methods:

### Via GitHub Repository (VCS)

Add the following to your project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/oqzl/GrokClient"
        }
    ],
    "require": {
        "oqzl/grok-client": "main"
    }
}
```

Then run:

```bash
composer update
```

### Via Local Development

For local development, you can install it by specifying the path:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../path/to/GrokClient"
        }
    ],
    "require": {
        "oqzl/grok-client": "*"
    }
}
```

## Usage

### Basic Usage

```php
<?php
use Oqzl\GrokClient\GrokClient;

// Initialize the client
$grok = GrokClient::new()->apiKey('your-api-key-here');

// Create a chat and get a response
$response = $grok->createChat('Tell me about quantum computing')->response();

echo $response;
```

### Continuing a Conversation

```php
<?php
use Xai\Grok\GrokClient;

$grok = GrokClient::new()->apiKey('your-api-key');

// Start a chat
$chat = $grok->createChat('What is the capital of France?');
echo $chat->response();

// Continue the conversation
$chat->reply('What about Japan?');
echo $chat->response();
```

### Configuration Options

```php
<?php
use Xai\Grok\GrokClient;

$grok = GrokClient::new()
    ->apiKey('your-api-key')
    ->model('grok-1') // Default model
    ->systemPrompt('You are a helpful AI assistant')
    ->temperature(0.7)
    ->maxTokens(1000);

$chat = $grok->createChat('Hello, Grok!');
```

### Response Formats

```php
<?php
use Xai\Grok\GrokClient;

$grok = GrokClient::new()->apiKey('your-api-key');
$chat = $grok->createChat('Explain quantum computing');

// Get just the text response
$textResponse = $chat->response();

// Get the message object (including role and content)
$messageObj = $chat->responseMessage();

// Get the full API response
$fullResponse = $chat->rawResponse();
```

## Requirements

- PHP 7.2 or later
- Guzzle HTTP client 7.0 or later

## License

MIT
