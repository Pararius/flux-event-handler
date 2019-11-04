<?php
declare(strict_types=1);

namespace TreeHouse\FluxEvent;

class StdoutNotifier implements Notifier
{
    public function notify(string $text)
    {
        echo $text;
    }
}
