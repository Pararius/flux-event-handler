<?php
declare(strict_types=1);

namespace TreeHouse\Notifier;

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
}
