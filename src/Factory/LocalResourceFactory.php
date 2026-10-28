<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Factory;

use Closure;
use SuperKernel\Filesystem\Contract\ReadableResourceInterface;
use SuperKernel\Filesystem\Contract\ResourceFactoryInterface;
use SuperKernel\Filesystem\Resource\BinaryStreamResource;
use SuperKernel\Filesystem\Resource\DirectoryResource;
use SuperKernel\Filesystem\Resource\FileResource;
use SuperKernel\Filesystem\Resource\StreamResource;

final class LocalResourceFactory implements ResourceFactoryInterface
{
    public function directory(string $path): ReadableResourceInterface
    {
        return new DirectoryResource($path);
    }

    public function file(string $path, int $chunkSize = 8192): ReadableResourceInterface
    {
        return new FileResource($path, $chunkSize);
    }

    public function stream(string $uri, int $chunkSize = 8192): ReadableResourceInterface
    {
        return StreamResource::fromUri($uri, $chunkSize);
    }

    public function streamFromFactory(
        Closure $opener,
        int     $chunkSize = 8192,
        string  $description = 'custom stream',
    ): ReadableResourceInterface
    {
        return StreamResource::fromFactory($opener, $chunkSize, $description);
    }

    public function binaryStream(string $contents, int $chunkSize = 8192): ReadableResourceInterface
    {
        return new BinaryStreamResource($contents, $chunkSize);
    }
}
