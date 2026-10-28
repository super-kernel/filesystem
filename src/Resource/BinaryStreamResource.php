<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Resource;

use SuperKernel\Filesystem\Contract\ReadableResourceInterface;
use SuperKernel\Filesystem\Exception\InvalidResourceConfigurationException;
use SuperKernel\Filesystem\Validation\ChunkSize;

use function strlen;
use function substr;

/**
 * @implements ReadableResourceInterface<string>
 */
final readonly class BinaryStreamResource implements ReadableResourceInterface
{
    private int $chunkSize;

    /**
     * @throws InvalidResourceConfigurationException When the chunk size is less than one
     */
    public function __construct(private string $contents, int            $chunkSize = 8192)
    {
        $this->chunkSize = ChunkSize::validate($chunkSize);
    }

    public function read(): iterable
    {
        $length = strlen($this->contents);

        for ($offset = 0; $offset < $length; $offset += $this->chunkSize) {
            yield substr($this->contents, $offset, $this->chunkSize);
        }
    }
}
