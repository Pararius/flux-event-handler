<?php
declare(strict_types=1);

namespace TreeHouse\Notifier;

class StdoutNotifier implements Notifier
{
    public function notify(string $text)
    {
        echo $text;
    }
}
