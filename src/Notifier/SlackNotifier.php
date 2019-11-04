<?php
declare(strict_types=1);

namespace TreeHouse\Notifier;

class SlackNotifier implements Notifier
{
    private $webhookUrl;

    public function __construct()
    {
        if (empty($_ENV['SLACK_WEBHOOK_URL'])) {
            throw new \RuntimeException('The SLACK_WEBHOOK_URL environment variable must be set.');
        }

        $this->webhookUrl = $_ENV['SLACK_WEBHOOK_URL'];
    }

    public function notify(string $text)
    {
        $options = [
            'http' => [
                'header'  => ['Content-type: application/json'],
                'method'  => 'POST',
                'content' => json_encode([
                    'text' => $text,
                    'username' => $_ENV['SLACK_USERNAME'] ?? 'Flux',
                    'icon_emoji' => $_ENV['SLACK_ICON'] ?? ':cloud:'
                ])
            ]
        ];

        return file_get_contents($this->webhookUrl, false, stream_context_create($options));
    }
}
