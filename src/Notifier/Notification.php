<?php
declare(strict_types=1);

namespace TreeHouse\Notifier;

use TreeHouse\FluxEvent\ProcessedPayload;

class Notification
{
    /** @var string */
    public $title;

    /** @var string */
    public $titleLink;

    /** @var string */
    public $body;

    public function __construct(string $title = null, string $titleLink = null, string $body = null)
    {
        $this->title = $title;
        $this->titleLink = $titleLink;
        $this->body = $body;
    }

    public static function fromProcessedPayload(ProcessedPayload $processedPayload, string $body = null): self
    {
        return new static($processedPayload->title, $processedPayload->titleLink, $body);
    }
}
