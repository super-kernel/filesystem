<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Constants;

enum DirectoryEntryType: string
{
    case Directory = 'directory';
    case File = 'file';
    case Link = 'link';
    case Other = 'other';
}
