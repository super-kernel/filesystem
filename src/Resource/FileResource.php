<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Resource;

use SuperKernel\Filesystem\Contract\ReadableResourceInterface;
use SuperKernel\Filesystem\Exception\InvalidResourceConfigurationException;
use SuperKernel\Filesystem\Exception\ResourceReadException;
use SuperKernel\Filesystem\Validation\ResourceLocation;

use function is_file;
use function sprintf;

/**
 * @implements ReadableResourceInterface<string>
 */
final readonly class FileResource implements ReadableResourceInterface
{
    private string $path;
    private StreamResource $stream;

    /**
     * @throws InvalidResourceConfigurationException When the path or chunk size is invalid
     */
    public function __construct(string $path, int $chunkSize = 8192)
    {
        $this->path = ResourceLocation::validate($path, 'File path');
        $this->stream = StreamResource::fromUri($this->path, $chunkSize);
    }

    public function read(): iterable
    {
        if (!is_file($this->path)) {
            throw new ResourceReadException(sprintf('File "%s" is not a regular file.', $this->path));
        }

        yield from $this->stream->read();
    }
}
