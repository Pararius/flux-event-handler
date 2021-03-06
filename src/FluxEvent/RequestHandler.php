<?php
declare(strict_types=1);

namespace TreeHouse\FluxEvent;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use TreeHouse\Notifier\GithubMapper;
use TreeHouse\Notifier\NamespaceMapper;
use TreeHouse\Notifier\Notification;
use TreeHouse\Notifier\Notifier;

class RequestHandler
{
    /** @var bool */
    private $debug;

    /** @var array */
    private $githubMapping;

    /** @var string */
    private $namespaceMapping;

    /** @var Notifier[] */
    private $notifiers;

    /** @var PayloadProcessor */
    private $processor;

    /** @var bool */
    private $shortImageNames;

    public function __construct(array $notifiers)
    {
        $this->debug = ($_SERVER['DEBUG'] == 1);
        $this->processor = new PayloadProcessor();
        $this->githubMapping = new GithubMapper($_SERVER['GITHUB_MAPPING'] ?? null);
        $this->namespaceMapping = $_SERVER['NAMESPACE_MAPPING'] ?? null;
        $this->shortImageNames = $_SERVER['SHORT_IMAGE_NAMES'] ?? "true";
        $this->notifiers = $notifiers;

        $this->verifyNotifiers($this->notifiers);
    }

    public function handle(Request $request)
    {
        $payload = '';
        if ($request->getMethod() === 'POST') {
            $request->getBody()->increaseSizeLimit(1048576);
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
                            foreach ($this->notifiers as $notifier) {
                                $notifier->notify(
                                    Notification::fromProcessedPayload($processedPayload, $response, $channel)
                                );
                            }
                        }
                    }
                } else {
                    // No namespace mapping defined, just process everything and send it to the default channel
                    $response = $this->createResponse($processedPayload);

                    if (!empty($response)) {
                        foreach ($this->notifiers as $notifier) {
                            $notifier->notify(Notification::fromProcessedPayload($processedPayload, $response));
                        }
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

            $bareImage = explode(':', $oldImage)[0];
            if ($this->shortImageNames === "true") {
                $oldImage = $this->shortImage($oldImage);
                $newImage = $this->shortImage($newImage);
            }

            list($oldImg, $oldTag) = explode(':', $oldImage);
            list($newImg, $newTag) = explode(':', $newImage);

            if (substr_count($bareImage, '/') < 2) {
                $bareImage = sprintf("https://hub.docker.com/r/%s", $bareImage);
            } else {
                $bareImage = sprintf("https://%s", $bareImage);
            }
            $img = sprintf("<%s|%s>", $bareImage, $oldImg);

            $key = sprintf('%s/%s', $workloadNamespace, $oldImg);
            if (array_key_exists($key, $this->githubMapping->githubMap)) {
                $githubRepo = $this->githubMapping->githubMap[$key];
                if (preg_match('/^[0-9a-f]{8}$/', $oldTag)) {
                    $oldTag = sprintf('<https://github.com/%s/commit/%s|%s>', $githubRepo, $oldTag, $oldTag);
                }
                if (preg_match('/^[0-9a-f]{8}$/', $newTag)) {
                    $newTag = sprintf('<https://github.com/%s/commit/%s|%s>', $githubRepo, $newTag, $newTag);
                }
                if (preg_match('/^v[0-9.]+$/', $oldTag)) {
                    $oldTag = sprintf('<https://github.com/%s/releases/tag/%s|%s>', $githubRepo, $oldTag, $oldTag);
                }
                if (preg_match('/^v[0-9.]+$/', $newTag)) {
                    $newTag = sprintf('<https://github.com/%s/releases/tag/%s|%s>', $githubRepo, $newTag, $newTag);
                }
            }
            if (is_null($namespace) || $workloadNamespace == $namespace) {
                $response .= sprintf(
                    '* [%s] %s updated from %s to %s',
                    $workloadNamespace,
                    $img,
                    $oldTag,
                    $newTag
                ) . PHP_EOL;
            }
        }

        return $response;
    }

    private function verifyNotifiers(array $notifiers)
    {
        if (empty($notifiers)) {
            throw new \RuntimeException('No Notifiers passed to RequestHandler');
        }

        foreach ($notifiers as $notifier) {
            if (!$notifier instanceof Notifier) {
                throw new \RuntimeException('Only Notifier instances may be passed to RequestHandler');
            }
        }
    }
}
