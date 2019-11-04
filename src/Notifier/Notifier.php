<?php
declare(strict_types=1);

namespace TreeHouse\Notifier;

interface Notifier
{
    public function notify(string $text);
}
