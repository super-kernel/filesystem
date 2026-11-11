<p align="right">
  <strong>English</strong> | <a href="README.zh-CN.md">中文文档</a>
</p>

# SuperKernel Filesystem

`super-kernel/filesystem` reads files, directories, and streams through one method, `read()`, which opens and closes the PHP handle itself, so code that reads never holds or closes one.

## Installation

```bash
composer require super-kernel/filesystem
```

The package has no requirement beyond PHP. Its classes are plain objects: they need no container and no build.

## What it provides

- File, stream, and in-memory resources that yield binary-safe chunks of a chosen size.
- Directory resources that yield the immediate entries with their names, paths, and types.
- A new read cycle for every `read()`, opened when iteration starts and closed when it ends, fails, or is abandoned.
- Streams from a PHP URI or from a factory of your own.
- `ResourceFactoryInterface`, so that code can depend on the contracts instead of the classes.

It does not:

- write, move, or delete files;
- read asynchronously or provide Swoole-specific resources;
- restrict, canonicalize, or expand paths, such as with glob patterns;
- register a container service.

## Quick start

Read a file in chunks of eight bytes. Save the script as `bin/read-file.php`:

```php
<?php

declare(strict_types=1);

use SuperKernel\Filesystem\Factory\LocalResourceFactory;

require dirname(__DIR__) . '/vendor/autoload.php';

$path = sys_get_temp_dir() . '/invoice-numbers.txt';
file_put_contents($path, "INV-1001\nINV-1002\n");

$file = new LocalResourceFactory()->file($path, chunkSize: 8);

foreach ($file->read() as $chunk) {
    echo '[', addcslashes($chunk, "\n"), ']', PHP_EOL;
}
```

Run the script:

```bash
php bin/read-file.php
```

It prints:

```text
[INV-1001]
[\nINV-100]
[2\n]
```

## Public surface

| Name | Kind | Purpose |
| --- | --- | --- |
| `SuperKernel\Filesystem\Contract\ResourceFactoryInterface` | Interface | Creates directory, file, stream, factory-stream, and in-memory resources. |
| `SuperKernel\Filesystem\Factory\LocalResourceFactory` | Class | The implementation of `ResourceFactoryInterface` for local files and PHP streams. |
| `SuperKernel\Filesystem\Contract\ReadableResourceInterface` | Interface | `read()` starts a new read cycle and yields the resource's values. |
| `SuperKernel\Filesystem\Resource\FileResource` | Class | Reads a regular file in chunks. |
| `SuperKernel\Filesystem\Resource\DirectoryResource` | Class | Reads the immediate entries of a directory. |
| `SuperKernel\Filesystem\Resource\StreamResource` | Class | Reads a stream opened from a URI with `fromUri()` or by a factory with `fromFactory()`. |
| `SuperKernel\Filesystem\Resource\BinaryStreamResource` | Class | Reads a string held in memory in chunks. |
| `SuperKernel\Filesystem\Model\DirectoryEntry` | Class | A directory entry: `name()`, `path()`, and `type()`. |
| `SuperKernel\Filesystem\Constants\DirectoryEntryType` | Enum | The entry type: `directory`, `file`, `link`, or `other`. |

## Behavior to know

- A chunk is a string of up to the chunk size, 8192 bytes by default; it implies no encoding or line boundary.
- Directory entries come in the filesystem's own, unspecified order, without `.` and `..`.
- Every exception implements `SuperKernel\Filesystem\Exception\FilesystemExceptionInterface`. An empty location or one with a null byte, or a chunk size below one, raises `InvalidResourceConfigurationException` when the resource is created, such as `Chunk size must be greater than zero.`
- A source that cannot be opened or read, including a file path that is not a regular file, raises `ResourceReadException` during `read()`, such as `File "<path>" is not a regular file.`
- A factory given to `streamFromFactory()` must return a newly opened stream on every call; the resource closes it after each read.

## Documentation

The developer documentation describes reading files, directories, streams, and memory, the lifetime of a read, and every error message, with complete examples: [SuperKernel Filesystem](https://github.com/super-kernel/.github/blob/main/docs/components/filesystem.md).

## License

Released under the MIT License. See [LICENSE](LICENSE).
