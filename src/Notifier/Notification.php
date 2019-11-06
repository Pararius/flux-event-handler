<?php
declare(strict_types=1);

namespace TreeHouse\Notifier;

use TreeHouse\FluxEvent\ProcessedPayload;

class Notification
{
    /** @var string */
    public $body;

    /** @var string */
    public $channel;

    /** @var string */
    public $title;

    /** @var string */
    public $titleLink;

    public function __construct(
        string $title = null,
        string $titleLink = null,
        string $body = null,
        string $channel = null
    ) {
        $this->title = $title;
        $this->titleLink = $titleLink;
        $this->body = $body;
        $this->channel = $channel;
    }

    public static function fromProcessedPayload(
        ProcessedPayload $processedPayload,
        string $body = null,
        string $channel = null
    ): self {
        return new static($processedPayload->title, $processedPayload->titleLink, $body, $channel);
    }
}
