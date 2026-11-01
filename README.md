<p align="right">
  <strong>English</strong> | <a href="README.zh-CN.md">中文文档</a>
</p>

# SuperKernel Filesystem

`super-kernel/filesystem` provides lifecycle-safe, read-only resource abstractions. Consumers call one behavior, `read()`, and never receive or close the backing PHP handle. It has no runtime dependency other than PHP.

## Reading resources

Use `LocalResourceFactory` when callers should depend only on factory and resource contracts:

```php
use SuperKernel\Filesystem\Factory\LocalResourceFactory;

$resources = new LocalResourceFactory();
$file = $resources->file('/srv/application/composer.json');

foreach ($file->read() as $chunk) {
    // $chunk is a binary-safe PHP string.
}
```

Each `read()` call starts a new read cycle. File and stream handles are opened only when iteration starts and are closed after exhaustion, a read failure, or release of a partially consumed iterator.

Directory resources yield immediate typed entries. Dot entries are omitted and iteration retains the filesystem's native, unspecified order:

```php
$directory = $resources->directory('/srv/application/src');

foreach ($directory->read() as $entry) {
    printf("%s %s\n", $entry->type()->value, $entry->path());
}
```

Streams opened from a PHP URI follow the same contract:

```php
$stream = $resources->stream('php://stdin', chunkSize: 16_384);

foreach ($stream->read() as $chunk) {
    // Process one chunk without managing the stream handle.
}
```

`binaryStream()` and `BinaryStreamResource` expose in-memory binary data through the same chunked behavior. PHP strings are binary-safe, so neither file nor stream chunks imply an encoding or line boundary.

For a custom native stream source, use `streamFromFactory()` or `StreamResource::fromFactory()`. The factory must return a newly opened PHP stream on every call; the resource owns and closes it.

## Errors

Every exception implements `Exception\FilesystemExceptionInterface`. An empty path, URI, or stream description, one containing a null byte, or a chunk size below one raises `InvalidResourceConfigurationException` when the resource is created. A source that cannot be opened or read, including a file path that is not a regular file, raises `ResourceReadException` during `read()`.

## Contract boundaries

`ReadableResourceInterface<TValue>` is generic because a directory produces `DirectoryEntry` values while byte-oriented resources produce strings. The common behavior is lifecycle-safe reading, not a forced common payload representation.

The package is synchronous and read-only. It does not provide write operations, asynchronous or Swoole-specific implementations, path containment policy, canonical path resolution, or glob matching.
