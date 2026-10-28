<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Validation;

use SuperKernel\Filesystem\Exception\InvalidResourceConfigurationException;

/**
 * Shared constructor validation for readable byte resources.
 *
 * @internal
 */
final class ChunkSize
{
    public static function validate(int $chunkSize): int
    {
        if (1 > $chunkSize) {
            throw new InvalidResourceConfigurationException('Chunk size must be greater than zero.');
        }

        return $chunkSize;
    }
}
