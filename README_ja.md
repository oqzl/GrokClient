# Grok Client

xAIのGrok APIと連携するための現代的なPHPクライアント。

## 特徴

- メソッドチェーンによる流暢なインターフェース
- シンプルなチャットベースのやり取り
- 複数の応答形式
- Composer互換
- PSR-4オートローディング

## インストール

このライブラリはPackagistに登録されていないため、以下の方法でインストールできる：

### GitHubリポジトリ経由（VCS）

プロジェクトの`composer.json`に以下を追加する：

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

その後、以下のコマンドを実行する：

```bash
composer update
```

### ローカル開発用

ローカル開発環境では、パスを指定してインストールできる：

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

## 使用例

```php
<?php

require 'vendor/autoload.php';

use Oqzl\GrokClient\GrokClient;

// クライアント初期化
$grok = GrokClient::new()
    ->apiKey('your-api-key')
    ->model('grok-1')
    ->systemPrompt('あなたは役立つAIアシスタントです。');

// チャット開始
$chat = $grok->createChat('こんにちは、Grokさん');
echo $chat->response();

// 会話を続ける
$chat->reply('東京の天気を教えてください');
echo $chat->response();

// 異なる応答形式
$messageObj = $chat->responseMessage(); // メッセージオブジェクト
$fullResponse = $chat->rawResponse();   // 完全なAPIレスポンス
```

## 要件

- PHP 7.2以上
- Guzzle HTTPクライアント7.0以上

## ライセンス

MIT
