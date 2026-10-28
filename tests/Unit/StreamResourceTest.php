<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Tests\Unit;

use RuntimeException;
use SuperKernel\Filesystem\Exception\ResourceReadException;
use SuperKernel\Filesystem\Resource\StreamResource;
use SuperKernel\Filesystem\Tests\Support\TestCase;

use function fclose;
use function fopen;
use function fwrite;
use function is_resource;
use function iterator_to_array;
use function rewind;

final class StreamResourceTest extends TestCase
{
    /**
     * PHP has no native resource return type, so resource factories must retain mixed.
     *
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function testOwnsAndClosesFactoryStreamsAfterEachRead(): void
    {
        $opened = null;
        $resource = StreamResource::fromFactory(
            /** @return resource|false */
            static function () use (&$opened): mixed {
                $opened = fopen('php://temp', 'w+b');

                if (false === $opened) {
                    return false;
                }

                fwrite($opened, "A\0BCD");
                rewind($opened);

                return $opened;
            },
            2,
            'test stream',
        );

        $this->assertSame([
            "A\0",
            'BC',
            'D',
        ], iterator_to_array($resource->read()));
        $this->assertFalse(is_resource($opened));

        $this->assertSame([
            "A\0",
            'BC',
            'D',
        ], iterator_to_array($resource->read()));
        $this->assertFalse(is_resource($opened));
    }

    /**
     * PHP has no native resource return type, so resource factories must retain mixed.
     *
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function testClosesStreamWhenPartiallyConsumedIteratorIsReleased(): void
    {
        $opened = null;
        $resource = StreamResource::fromFactory(
            /** @return resource|false */
            static function () use (&$opened): mixed {
                $opened = fopen('php://temp', 'w+b');

                if (false === $opened) {
                    return false;
                }

                fwrite($opened, 'abcdef');
                rewind($opened);

                return $opened;
            },
            2,
            'partial stream',
        );
        $iterator = $resource->read();

        foreach ($iterator as $chunk) {
            $this->assertSame('ab', $chunk);
            break;
        }

        $this->assertTrue(is_resource($opened));
        unset($iterator);
        $this->assertFalse(is_resource($opened));
    }

    /**
     * PHP has no native resource return type, so resource factories must retain mixed.
     *
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function testReleasesAnIteratorWhoseStreamWasAlreadyClosed(): void
    {
        $opened = null;
        $resource = StreamResource::fromFactory(
            /** @return resource|false */
            static function () use (&$opened): mixed {
                $opened = fopen('php://temp', 'w+b');

                if (false === $opened) {
                    return false;
                }

                fwrite($opened, 'abcdef');
                rewind($opened);

                return $opened;
            },
            2,
        );
        $iterator = $resource->read();

        foreach ($iterator as $chunk) {
            $this->assertSame('ab', $chunk);
            break;
        }

        $this->assertIsResource($opened);
        fclose($opened);
        unset($iterator);
        $this->assertFalse(is_resource($opened));
    }

    public function testRejectsFactoryValuesThatAreNotStreams(): void
    {
        $resource = StreamResource::fromFactory(static fn(): bool => false);

        $this->assertThrows(
            ResourceReadException::class,
            static fn(): array => iterator_to_array($resource->read()),
            'Unable to open custom stream',
        );
    }

    public function testWrapsStreamFactoryFailures(): void
    {
        $cause = new RuntimeException('factory failed');
        $resource = StreamResource::fromFactory(
            static function () use ($cause): never {
                throw $cause;
            },
        );
        $exception = $this->assertThrows(
            ResourceReadException::class,
            static fn(): array => iterator_to_array($resource->read()),
            'Unable to open custom stream',
        );

        $this->assertSame($cause, $exception->getPrevious());
    }
}
