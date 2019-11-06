<?php
declare(strict_types=1);

namespace TreeHouse\FluxEvent;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use TreeHouse\Notifier\NamespaceMapper;
use TreeHouse\Notifier\Notification;
use TreeHouse\Notifier\Notifier;

class RequestHandler
{
    /** @var bool */
    private $debug;

    /** @var string */
    private $namespaceMapping;

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
        $this->namespaceMapping = $_SERVER['NAMESPACE_MAPPING'] ?? null;
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

                if (!empty($this->namespaceMapping)) {
                    // Parse namespace mapping and send notifications to all appropriate channels
                    $mapper = new NamespaceMapper($this->namespaceMapping);
                    foreach ($mapper->namespaceMap as $channel => $namespace) {
                        if ($namespace == '*') {
                            // Treat wildcard namespace as null value, so no filtering is applied
                            $namespace = null;
                        }

                        $response = $this->createResponse($processedPayload, $namespace);

                        if (!empty($response)) {
                            $this->notifier->notify(
                                Notification::fromProcessedPayload($processedPayload, $response, $channel)
                            );
                        }
                    }
                } else {
                    // No namespace mapping defined, just process everything and send it to the default channel
                    $response = $this->createResponse($processedPayload);

                    if (!empty($response)) {
                        $this->notifier->notify(Notification::fromProcessedPayload($processedPayload, $response));
                    }
                }
            } catch (\RuntimeException $e) {
                $response = $e->getMessage();
            }
        }

        $responseText = ($this->debug) ? $payload . PHP_EOL . $response : $response;
        return new Response(Status::OK, ["content-type" => "text/plain; charset=utf-8"], $responseText);
    }

    private function shortImage(string $image): string
    {
        if (($pos = strrpos($image, '/')) !== false) {
            return substr($image, ++$pos);
        }

        return $image;
    }

    private function createResponse(ProcessedPayload $processedPayload, string $namespace = null): string
    {
        $response = '';
        foreach ($processedPayload->changes as $oldImage => $newImage) {
            $workloadNamespace = $processedPayload->namespaces[$oldImage];

            if ($this->shortImageNames) {
                $oldImage = $this->shortImage($oldImage);
                $newImage = $this->shortImage($newImage);
            }

            if (is_null($namespace) || $workloadNamespace == $namespace) {
                $response .= sprintf(
                    '* [%s] %s updated to %s',
                    $workloadNamespace,
                    $oldImage,
                    $newImage
                ) . PHP_EOL;
            }
        }

        return $response;
    }
}
