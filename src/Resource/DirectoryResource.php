<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Resource;

use SuperKernel\Filesystem\Contract\ReadableResourceInterface;
use SuperKernel\Filesystem\Enum\DirectoryEntryType;
use SuperKernel\Filesystem\Exception\InvalidResourceConfigurationException;
use SuperKernel\Filesystem\Exception\ResourceReadException;
use SuperKernel\Filesystem\Model\DirectoryEntry;
use SuperKernel\Filesystem\Validation\ResourceLocation;

use function closedir;
use function is_dir;
use function is_file;
use function is_link;
use function opendir;
use function readdir;
use function rtrim;
use function sprintf;

use const DIRECTORY_SEPARATOR;

/**
 * @implements ReadableResourceInterface<DirectoryEntry>
 */
final readonly class DirectoryResource implements ReadableResourceInterface
{
    private string $path;

    /**
     * @throws InvalidResourceConfigurationException When the path is empty or contains a null byte
     */
    public function __construct(string $path)
    {
        $this->path = ResourceLocation::validate($path, 'Directory path');
    }

    public function read(): iterable
    {
        $handle = @opendir($this->path);

        if (false === $handle) {
            throw new ResourceReadException(sprintf('Unable to open directory "%s".', $this->path));
        }

        try {
            while (($name = readdir($handle)) !== false) {
                if ('.' === $name || '..' === $name) {
                    continue;
                }

                $path = $this->entryPath($name);

                yield new DirectoryEntry($name, $path, self::entryType($path));
            }
        } finally {
            closedir($handle);
        }
    }

    private function entryPath(string $name): string
    {
        $base = rtrim($this->path, '/\\');

        return ('' === $base ? DIRECTORY_SEPARATOR : $base . DIRECTORY_SEPARATOR) . $name;
    }

    private static function entryType(string $path): DirectoryEntryType
    {
        if (is_link($path)) {
            return DirectoryEntryType::Link;
        }

        if (is_dir($path)) {
            return DirectoryEntryType::Directory;
        }

        if (is_file($path)) {
            return DirectoryEntryType::File;
        }

        return DirectoryEntryType::Other;
    }
}
