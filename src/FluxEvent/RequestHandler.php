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

    /** @var Notifier */
    private $notifier;

    /** @var PayloadProcessor */
    private $processor;

    /** @var bool */
    private $shortImageNames;

    public function __construct(Notifier $notifier)
    {
        $this->debug = ($_SERVER['DEBUG'] == 1);
        $this->processor = new PayloadProcessor();
        $this->notifier = $notifier;
        $this->shortImageNames = $_SERVER['SHORT_IMAGE_NAMES'] ?? true;
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
                $response = $this->createResponse($processedPayload);

                $this->notifier->notify(Notification::fromProcessedPayload($processedPayload, $response));
            } catch (\RuntimeException $e) {
                $response = $e->getMessage();
            }
        }

        $responseText = ($this->debug) ? $payload . PHP_EOL . $response : $response;
        return new Response(Status::OK, ["content-type" => "text/plain; charset=utf-8"], $responseText);
    }

    private function shortImage(string $image)
    {
        if (($pos = strrpos($image, '/')) !== false) {
            return substr($image, ++$pos);
        }

        return $image;
    }

    private function createResponse(ProcessedPayload $processedPayload): string
    {
        $response = '';
        foreach ($processedPayload->changes as $oldImage => $newImage) {
            if ($this->shortImageNames) {
                $fullImage = $oldImage;
                $oldImage = $this->shortImage($oldImage);
                $newImage = $this->shortImage($newImage);
            }

            $response .= sprintf(
                    '* [%s] %s updated to %s',
                    $processedPayload->namespaces[$fullImage ?? $oldImage],
                    $oldImage,
                    $newImage
                ) . PHP_EOL;
        }

        return $response;
    }
}
