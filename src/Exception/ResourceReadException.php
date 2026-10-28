<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Exception;

use RuntimeException;

final class ResourceReadException extends RuntimeException implements FilesystemExceptionInterface
{
}
