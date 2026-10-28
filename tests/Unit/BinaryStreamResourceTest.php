<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Tests\Unit;

use SuperKernel\Filesystem\Exception\InvalidResourceConfigurationException;
use SuperKernel\Filesystem\Resource\BinaryStreamResource;
use SuperKernel\Filesystem\Tests\Support\TestCase;

use function iterator_to_array;

final class BinaryStreamResourceTest extends TestCase
{
    public function testReadsBinarySafeRepeatableChunks(): void
    {
        $resource = new BinaryStreamResource("A\0BCDE", 2);
        $expected = [
            "A\0",
            'BC',
            'DE',
        ];

        $this->assertSame($expected, iterator_to_array($resource->read()));
        $this->assertSame($expected, iterator_to_array($resource->read()));
    }

    public function testEmptyInputProducesNoChunks(): void
    {
        $this->assertSame([], iterator_to_array(new BinaryStreamResource('')->read()));
    }

    public function testRejectsInvalidChunkSize(): void
    {
        $this->assertThrows(
            InvalidResourceConfigurationException::class,
            static fn(): BinaryStreamResource => new BinaryStreamResource('data', 0),
        );
    }
}
