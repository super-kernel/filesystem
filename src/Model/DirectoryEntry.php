<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Model;

use SuperKernel\Filesystem\Enum\DirectoryEntryType;

final readonly class DirectoryEntry
{
    public function __construct(
        private string             $name,
        private string             $path,
        private DirectoryEntryType $type,
    )
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function type(): DirectoryEntryType
    {
        return $this->type;
    }
}
