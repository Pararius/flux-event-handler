<?php
declare(strict_types=1);

namespace Tests\FluxEvent;

use PHPUnit\Framework\TestCase;
use TreeHouse\FluxEvent\PayloadProcessor;

class PayloadProcessorTest extends TestCase
{
    public function testProcess()
    {
        $processor = new PayloadProcessor();
        $result = $processor->process(file_get_contents(__DIR__ . '/../../samples/commit.json'));

        $this->assertSame(
            [
                'title' => 'Applied flux changes to cluster',
                'titleLink' => 'https://github.com/octocat/Hello-World/commit/7fd1a60b01f91b314f59955a4e4d4e80d8edf11d',
                'changes' => ['ubuntu:19.04' => 'ubuntu:19.10', 'postgres:11.5' => 'postgres:12.0']
            ],
            $result
        );
    }
}
