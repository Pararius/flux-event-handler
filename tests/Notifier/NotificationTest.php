<?php
declare(strict_types=1);

namespace Tests\Notifier;

use PHPUnit\Framework\TestCase;
use TreeHouse\FluxEvent\ProcessedPayload;
use TreeHouse\Notifier\Notification;

class NotificationTest extends TestCase
{
    public function testFromProcessedPayload()
    {
        $payload = new ProcessedPayload();
        $payload->title = 'Something happened';
        $payload->titleLink = 'http://www.example.com/';

        $expected = new Notification($payload->title, $payload->titleLink, 'Some message', '#test');
        $notification = Notification::fromProcessedPayload($payload, 'Some message', '#test');

        $this->assertEquals($expected, $notification);
    }
}
