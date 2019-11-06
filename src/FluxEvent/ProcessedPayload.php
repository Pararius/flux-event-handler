<?php
declare(strict_types=1);

namespace TreeHouse\FluxEvent;

class ProcessedPayload
{
    /** @var string */
    public $title;

    /** @var string */
    public $titleLink;

    /** @var array */
    public $changes;

    /** @var array */
    public $namespaces;
}
