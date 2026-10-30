# SuperKernel Filesystem

`super-kernel/filesystem` provides lifecycle-safe, read-only resource abstractions. Consumers call one behavior,
`read()`, and never receive or close the backing PHP handle.

The package requires PHP 8.4.27 or later in the 8.4.x series and has no runtime dependency other than PHP.

## Source organization

Paths below are relative to `src/` and the package namespace.

| Directory                      | Responsibility                                                                |
|--------------------------------|-------------------------------------------------------------------------------|
| `Factory`                      | `LocalResourceFactory`: constructs readable resources.                        |
| `Resource`                     | Directory, file, native-stream, and byte-string readers.                      |
| `Validation`                   | `ChunkSize` and `ResourceLocation`: constructor invariants shared by readers. |
| `Model / Constants / Contract` | Directory entry values, entry kinds, and resource contracts.                  |

Implementation helpers retain an `@internal` boundary; a responsibility namespace does not make them extension points.

### Public namespaces

Use the following component declarations:

- `Validation\ChunkSize`
- `Validation\ResourceLocation`
- `Factory\LocalResourceFactory`

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

Each `read()` call starts a new read cycle. File and stream handles are opened only when iteration starts and are closed
after exhaustion, a read failure, or release of a partially consumed iterator.

Directory resources yield immediate typed entries. Dot entries are omitted and iteration retains the filesystem's native,
unspecified order:

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

`BinaryStreamResource` exposes in-memory binary data through the same chunked behavior. PHP strings are binary-safe, so
neither file nor stream chunks imply an encoding or line boundary.

For a custom native stream source, use `StreamResource::fromFactory()`. The factory must return a newly opened PHP stream
on every call; `StreamResource` owns and closes it.

## Contract boundaries

`ReadableResourceInterface<TValue>` is generic because a directory produces `DirectoryEntry` values while byte-oriented
resources produce strings. The common behavior is lifecycle-safe reading, not a forced common payload representation.

This initial package is synchronous and read-only. It does not currently provide write operations, asynchronous or
Swoole-specific implementations, path containment policy, canonical path resolution, or glob matching.

## Composer metadata integration

`AutoloadRuleParser` in `super-kernel/composer` uses path classification, canonical resolution, glob expansion, and
symbolic-link containment checks. Those operations are intentionally not placed on the readable-resource interface.
The Composer component remains unchanged until a separate filesystem-query contract and its exact compatibility
semantics are approved.

## Development

Use a PHP 8.4 Composer in this package's checkout:

```bash
composer install
composer validate --strict
composer test
composer format:check
composer format
```
