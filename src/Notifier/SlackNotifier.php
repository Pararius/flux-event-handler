<?php
declare(strict_types=1);

namespace TreeHouse\Notifier;

class SlackNotifier implements Notifier
{
    private $webhookUrl;

    public function __construct()
    {
        if (empty($_SERVER['SLACK_WEBHOOK_URL'])) {
            throw new \RuntimeException('The SLACK_WEBHOOK_URL environment variable must be set.');
        }

        $this->webhookUrl = $_SERVER['SLACK_WEBHOOK_URL'];
    }

    public function notify(Notification $notification)
    {
        // json_encode messes up the payload and makes it invalid, so build it manually
        $json = sprintf(
            '{
                "attachments": [{"fallback": "%s", "title": "%s", "title_link": "%s", "text": "%s"}],
                "username": "%s",
                "icon_emoji": "%s"
            }',
            $notification->body, $notification->title, $notification->titleLink, $notification->body,
            $_SERVER['SLACK_USERNAME'] ?? 'Flux',
            $_SERVER['SLACK_ICON'] ?? ':cloud:'
        );

        $options = [
            'http' => [
                'header'  => ['Content-type: application/json'],
                'method'  => 'POST',
                'content' => $json,
            ]
        ];

        return file_get_contents($this->webhookUrl, false, stream_context_create($options));
    }
}
