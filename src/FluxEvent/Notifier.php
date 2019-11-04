<?php
declare(strict_types=1);

namespace TreeHouse\FluxEvent;

interface Notifier
{
    public function notify(string $text);
}
