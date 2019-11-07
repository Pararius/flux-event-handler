<?php
declare(strict_types=1);

namespace Tests\FluxEvent;

use PHPUnit\Framework\TestCase;
use TreeHouse\FluxEvent\RequestHandler;
use TreeHouse\Notifier\SlackNotifier;
use TreeHouse\Notifier\StdoutNotifier;

class RequestHandlerTest extends TestCase
{
    public function setUp(): void
    {
        // Set dummy env vars
        $_SERVER['DEBUG'] = 0;
        $_SERVER['SLACK_WEBHOOK_URL'] = 'http://localhost:80';
    }

    public function testValidNotifiers()
    {
        $requestHandler = new RequestHandler([new StdoutNotifier(), new SlackNotifier()]);

        $this->assertInstanceOf(RequestHandler::class, $requestHandler);
    }

    public function testInvalidNotifiers()
    {
        $this->expectExceptionMessage('Only Notifier instances may be passed to RequestHandler');
        new RequestHandler([new StdoutNotifier(), 'SlackNotifier']);

        $this->expectExceptionMessage('No Notifiers passed to RequestHandler');
        new RequestHandler([]);
    }
}
