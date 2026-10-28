<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Enum;

enum DirectoryEntryType: string
{
    case Directory = 'directory';
    case File = 'file';
    case Link = 'link';
    case Other = 'other';
}
