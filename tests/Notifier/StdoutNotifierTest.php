<?php
declare(strict_types=1);

namespace Tests\Notifier;

use PHPUnit\Framework\TestCase;
use TreeHouse\Notifier\Notification;
use TreeHouse\Notifier\StdoutNotifier;

class StdoutNotifierTest extends TestCase
{
    public function testNotify()
    {
        $notification = new Notification('Title', 'http://www.example.com/', 'Some notification', '#test');
        $notifier = new StdoutNotifier();
        $notifier->notify($notification);
        $this->expectOutputString($notification->body);
    }
}
