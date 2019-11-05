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
                'changes' => [
                    'docker.io/ubuntu:19.04' => 'docker.io/ubuntu:19.10',
                    'docker.io/postgres:11.5' => 'docker.io/postgres:12.0'
                ],
                'namespaces' => [
                    'docker.io/ubuntu:19.04' => 'namespace',
                    'docker.io/postgres:11.5' => 'namespace'
                ]
            ],
            $result
        );
    }
}
