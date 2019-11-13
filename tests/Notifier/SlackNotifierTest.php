<?php
declare(strict_types=1);

namespace Tests\Notifier;

use PHPUnit\Framework\TestCase;
use TreeHouse\Notifier\Notification;
use TreeHouse\Notifier\SlackNotifier;

class SlackNotifierTest extends TestCase
{
    public function testMissingWebhookUrl()
    {
        unset($_SERVER['SLACK_WEBHOOK_URL']);
        $this->expectExceptionMessage('The SLACK_WEBHOOK_URL environment variable must be set.');
        new SlackNotifier();
    }

    public function testNotify()
    {
        $_SERVER['SLACK_WEBHOOK_URL'] = '/dev/null';
        $notification = new Notification('Title', 'http://www.example.com/', 'Some notification', '#test');
        $notifier = new SlackNotifier();
        $result = $notifier->notify($notification);
        $this->assertNotFalse($result);
    }
}
