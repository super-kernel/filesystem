<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Tests\Unit;

use SuperKernel\Filesystem\Contract\ReadableResourceInterface;
use SuperKernel\Filesystem\Exception\InvalidResourceConfigurationException;
use SuperKernel\Filesystem\Factory\LocalResourceFactory;
use SuperKernel\Filesystem\Resource\BinaryStreamResource;
use SuperKernel\Filesystem\Resource\DirectoryResource;
use SuperKernel\Filesystem\Resource\FileResource;
use SuperKernel\Filesystem\Resource\StreamResource;
use SuperKernel\Filesystem\Tests\Support\TestCase;

use function fopen;

final class LocalResourceFactoryTest extends TestCase
{
    /**
     * PHP has no native resource return type, so resource factories must retain mixed.
     *
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function testCreatesEverySupportedResourceBehindTheCommonContract(): void
    {
        $factory = new LocalResourceFactory();
        $resources = [
            DirectoryResource::class    => $factory->directory('/tmp'),
            FileResource::class         => $factory->file('/tmp/example'),
            StreamResource::class       => $factory->stream('php://temp'),
            BinaryStreamResource::class => $factory->binaryStream('data'),
            'custom'                    => $factory->streamFromFactory(
                /** @return resource|false */
                static fn(): mixed => fopen('php://temp', 'w+b'),
            ),
        ];

        foreach ($resources as $class => $resource) {
            $this->assertInstanceOf(ReadableResourceInterface::class, $resource);

            if ('custom' !== $class) {
                $this->assertInstanceOf($class, $resource);
            }
        }
    }

    /**
     * PHP has no native resource return type, so resource factories must retain mixed.
     *
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function testRejectsInvalidResourceConfigurationAtCreation(): void
    {
        $factory = new LocalResourceFactory();
        $cases = [
            static fn(): ReadableResourceInterface => $factory->directory(''),
            static fn(): ReadableResourceInterface => $factory->file("invalid\0path"),
            static fn(): ReadableResourceInterface => $factory->stream(''),
            static fn(): ReadableResourceInterface => $factory->streamFromFactory(
                /** @return resource|false */
                static fn(): mixed => fopen('php://temp', 'w+b'),
                description: '',
            ),
            static fn(): ReadableResourceInterface => $factory->binaryStream('data', 0),
        ];

        foreach ($cases as $case) {
            $this->assertThrows(InvalidResourceConfigurationException::class, $case);
        }
    }
}
