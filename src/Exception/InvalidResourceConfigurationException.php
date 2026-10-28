<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Exception;

use InvalidArgumentException;

final class InvalidResourceConfigurationException extends InvalidArgumentException implements FilesystemExceptionInterface
{
}
