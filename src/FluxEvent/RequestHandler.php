<?php
declare(strict_types=1);

namespace TreeHouse\FluxEvent;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use TreeHouse\Notifier\Notifier;

class RequestHandler
{
    /** @var PayloadProcessor */
    private $processor;

    /** @var Notifier */
    private $notifier;

    public function __construct(Notifier $notifier)
    {
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
                $changes = $this->processor->process($payload);

                $response = '';
                foreach ($changes as $oldImage => $newImage) {
                    $response .= sprintf('* %s updated to %s', $oldImage, $newImage) . PHP_EOL;
                }

                $this->notifier->notify($response);
            } catch (\RuntimeException $e) {
                $response = $e->getMessage();
            }
        }

        $responseText = ($_ENV['DEBUG'] == 1) ? $payload . PHP_EOL . $response : $response;
        return new Response(Status::OK, ["content-type" => "text/plain; charset=utf-8"], $responseText);
    }
}
