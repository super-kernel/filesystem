<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Resource;

use Closure;
use SuperKernel\Filesystem\Contract\ReadableResourceInterface;
use SuperKernel\Filesystem\Exception\InvalidResourceConfigurationException;
use SuperKernel\Filesystem\Exception\ResourceReadException;
use SuperKernel\Filesystem\Validation\ChunkSize;
use SuperKernel\Filesystem\Validation\ResourceLocation;
use Throwable;

use function fclose;
use function feof;
use function fopen;
use function fread;
use function get_resource_type;
use function is_resource;
use function sprintf;

/**
 * @implements ReadableResourceInterface<string>
 */
final readonly class StreamResource implements ReadableResourceInterface
{
    /**
     * @param Closure(): mixed $opener
     */
    private function __construct(private Closure $opener, private int     $chunkSize, private string  $description)
    {
    }

    /**
     * @throws InvalidResourceConfigurationException When the URI or chunk size is invalid
     */
    public static function fromUri(string $uri, int $chunkSize = 8192): self
    {
        $uri = ResourceLocation::validate($uri, 'Stream URI');

        return new self(
            /** @return resource|false */
            static fn() => @fopen($uri, 'rb'),
            ChunkSize::validate($chunkSize),
            sprintf('stream "%s"', $uri),
        );
    }

    /**
     * @param Closure(): mixed $opener Must return a newly opened PHP stream owned by this object
     *
     * @throws InvalidResourceConfigurationException When the description or chunk size is invalid
     */
    public static function fromFactory(
        Closure $opener,
        int     $chunkSize = 8192,
        string  $description = 'custom stream',
    ): self
    {
        return new self(
            $opener,
            ChunkSize::validate($chunkSize),
            ResourceLocation::validate($description, 'Stream description'),
        );
    }

    public function read(): iterable
    {
        try {
            $stream = ($this->opener)();
        } catch (Throwable $throwable) {
            throw new ResourceReadException(
                sprintf('Unable to open %s.', $this->description),
                previous: $throwable,
            );
        }

        if (!is_resource($stream) || 'stream' !== get_resource_type($stream)) {
            throw new ResourceReadException(sprintf('Unable to open %s.', $this->description));
        }

        try {
            while (!feof($stream)) {
                $chunk = @fread($stream, $this->chunkSize);

                if (false === $chunk) {
                    throw new ResourceReadException(sprintf('Unable to read %s.', $this->description));
                }

                if ('' === $chunk) {
                    if (feof($stream)) {
                        break;
                    }

                    throw new ResourceReadException(sprintf(
                        'Reading %s made no progress before end of stream.',
                        $this->description,
                    ));
                }

                yield $chunk;
            }
        } finally {
            self::closeStream($stream);
        }
    }

    /**
     * Closes the stream unless it was already closed while iteration was suspended.
     */
    private static function closeStream(mixed $stream): void
    {
        if (is_resource($stream)) {
            fclose($stream);
        }
    }
}
