<?php
declare(strict_types=1);

namespace TreeHouse\FluxEvent;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use TreeHouse\Notifier\Notification;
use TreeHouse\Notifier\Notifier;

class RequestHandler
{
    /** @var bool */
    private $debug;

    /** @var PayloadProcessor */
    private $processor;

    /** @var Notifier */
    private $notifier;

    public function __construct(Notifier $notifier)
    {
        $this->debug = ($_ENV['DEBUG'] == 1);
        $this->processor = new PayloadProcessor();
        $this->notifier = $notifier;
    }

    public function handle(Request $request)
    {
        $payload = '';
        if ($request->getMethod() === 'POST') {
            while (($data = yield $request->getBody()->read()) !== null) {
                $payload .= $data;
            }

            try {
                $processedPayload = $this->processor->process($payload);

                $response = '';
                foreach ($processedPayload['changes'] as $oldImage => $newImage) {
                    $response .= sprintf('* %s updated to %s', $oldImage, $newImage) . PHP_EOL;
                }

                $notification = new Notification();

                if (!$this->debug) {
                    $notification->title = $processedPayload['title'];
                    $notification->titleLink = $processedPayload['titleLink'];
                }

                $notification->body = $response;

                $this->notifier->notify($notification);
            } catch (\RuntimeException $e) {
                $response = $e->getMessage();
            }
        }

        $responseText = ($this->debug) ? $payload . PHP_EOL . $response : $response;
        return new Response(Status::OK, ["content-type" => "text/plain; charset=utf-8"], $responseText);
    }
}
