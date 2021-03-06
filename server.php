<?php
require __DIR__ . '/vendor/autoload.php';

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Server;
use Amp\Socket;
use TreeHouse\FluxEvent\RequestHandler;
use TreeHouse\Log\IoLogger;
use TreeHouse\Notifier\SlackNotifier;
use TreeHouse\Notifier\StdoutNotifier;

Amp\Loop::run(function () {
    $sockets = [Socket\listen('0.0.0.0:80')];
    $server = new Server($sockets, new CallableRequestHandler(
        function(Request $request) {
            $debug = (isset($_SERVER['DEBUG']) && $_SERVER['DEBUG'] == 1);
            $notifiers = [];

            if ($debug) {
                $notifiers[] = new StdoutNotifier();
            }

            if (isset($_SERVER['SLACK_WEBHOOK_URL'])) {
                $notifiers[] = new SlackNotifier();
            }

            $requestHandler = new RequestHandler($notifiers);
            return $requestHandler->handle($request);
        }
    ), new IoLogger());

    yield $server->start();
});
