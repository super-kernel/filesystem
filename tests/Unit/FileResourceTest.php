<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Tests\Unit;

use SuperKernel\Filesystem\Exception\ResourceReadException;
use SuperKernel\Filesystem\Resource\FileResource;
use SuperKernel\Filesystem\Tests\Support\TestCase;
use Throwable;

use function iterator_to_array;

final class FileResourceTest extends TestCase
{
    /** @throws Throwable When fixture setup or the operation under test throws */
    public function testReadsBinarySafeRepeatableFileChunks(): void
    {
        $this->withTemporaryDirectory(function (
            string $directory,
        ): void {
            $path = $directory . '/payload.bin';
            $this->writeFile($path, "A\0BCDE");
            $resource = new FileResource($path, 2);
            $expected = [
                "A\0",
                'BC',
                'DE',
            ];

            $this->assertSame($expected, iterator_to_array($resource->read()));
            $this->assertSame($expected, iterator_to_array($resource->read()));
        });
    }

    /** @throws Throwable When fixture setup or the operation under test throws */
    public function testRejectsDirectoriesWhenReadingBegins(): void
    {
        $this->withTemporaryDirectory(function (
            string $directory,
        ): void {
            $resource = new FileResource($directory);

            $this->assertThrows(
                ResourceReadException::class,
                static fn(): array => iterator_to_array($resource->read()),
                'is not a regular file',
            );
        });
    }
}
