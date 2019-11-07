<?php
declare(strict_types=1);

namespace Tests\Notifier;

use PHPUnit\Framework\TestCase;
use TreeHouse\Notifier\NamespaceMapper;

class NamespaceMapperTest extends TestCase
{
    public function testValidMapping()
    {
        $mapping = '#foo=bar,#bar=foo';
        $mapper = new NamespaceMapper($mapping);

        $expected = ['#foo' => 'bar', '#bar' => 'foo'];

        $this->assertSame($expected, $mapper->namespaceMap);
    }

    public function testInvalidMapping()
    {
        $mapping = '#foo:bar,#bar:foo';
        $this->expectException(\RuntimeException::class);
        new NamespaceMapper($mapping);
    }
}
