<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Validation;

use SuperKernel\Filesystem\Exception\InvalidResourceConfigurationException;

use function sprintf;
use function str_contains;

/**
 * Shared location validation for local readable resources.
 *
 * @internal
 */
final class ResourceLocation
{
    public static function validate(string $location, string $label): string
    {
        if ('' === $location) {
            throw new InvalidResourceConfigurationException(sprintf('%s must not be empty.', $label));
        }

        if (str_contains($location, "\0")) {
            throw new InvalidResourceConfigurationException(sprintf('%s must not contain null bytes.', $label));
        }

        return $location;
    }
}
