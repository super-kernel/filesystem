<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Contract;

use Closure;
use SuperKernel\Filesystem\Exception\InvalidResourceConfigurationException;
use SuperKernel\Filesystem\Model\DirectoryEntry;

interface ResourceFactoryInterface
{
    /**
     * @return ReadableResourceInterface<DirectoryEntry>
     *
     * @throws InvalidResourceConfigurationException When the path is empty or contains a null byte
     */
    public function directory(string $path): ReadableResourceInterface;

    /**
     * @return ReadableResourceInterface<string>
     *
     * @throws InvalidResourceConfigurationException When the path or chunk size is invalid
     */
    public function file(string $path, int $chunkSize = 8192): ReadableResourceInterface;

    /**
     * @return ReadableResourceInterface<string>
     *
     * @throws InvalidResourceConfigurationException When the URI or chunk size is invalid
     */
    public function stream(string $uri, int $chunkSize = 8192): ReadableResourceInterface;

    /**
     * @param Closure(): mixed $opener Must return a newly opened PHP stream owned by the resource
     *
     * @return ReadableResourceInterface<string>
     *
     * @throws InvalidResourceConfigurationException When the description or chunk size is invalid
     */
    public function streamFromFactory(
        Closure $opener,
        int     $chunkSize = 8192,
        string  $description = 'custom stream',
    ): ReadableResourceInterface;

    /**
     * @return ReadableResourceInterface<string>
     *
     * @throws InvalidResourceConfigurationException When the chunk size is invalid
     */
    public function binaryStream(string $contents, int $chunkSize = 8192): ReadableResourceInterface;
}
