<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Contract;

use SuperKernel\Filesystem\Exception\ResourceReadException;

/**
 * Reads values while keeping creation and closure of backing handles inside the implementation.
 *
 * @template-covariant TValue
 */
interface ReadableResourceInterface
{
    /**
     * Starts a new lazy read cycle owned by the implementation.
     *
     * @return iterable<TValue>
     *
     * @throws ResourceReadException When the backing source cannot be opened or read
     */
    public function read(): iterable;
}
