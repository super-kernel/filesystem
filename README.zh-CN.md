<p align="right">
  <a href="README.md">English</a> | <strong>中文文档</strong>
</p>

# SuperKernel Filesystem

`super-kernel/filesystem` 提供生命周期安全的只读资源抽象。使用方只调用一个行为 `read()`，从不接触也不需要关闭底层的 PHP 句柄。除 PHP 外，它没有其他运行时依赖。

## 读取资源

当调用方只应依赖工厂和资源契约时，使用 `LocalResourceFactory`：

```php
use SuperKernel\Filesystem\Factory\LocalResourceFactory;

$resources = new LocalResourceFactory();
$file = $resources->file('/srv/application/composer.json');

foreach ($file->read() as $chunk) {
    // $chunk is a binary-safe PHP string.
}
```

每次调用 `read()` 都会开始一个新的读取周期。文件和流句柄只在迭代开始时打开，并在迭代结束、读取失败或部分消费的迭代器被释放时关闭。

目录资源直接产出类型化的条目。`.` 和 `..` 会被省略，迭代保持文件系统原生的、不确定的顺序：

```php
$directory = $resources->directory('/srv/application/src');

foreach ($directory->read() as $entry) {
    printf("%s %s\n", $entry->type()->value, $entry->path());
}
```

从 PHP URI 打开的流遵循同样的契约：

```php
$stream = $resources->stream('php://stdin', chunkSize: 16_384);

foreach ($stream->read() as $chunk) {
    // Process one chunk without managing the stream handle.
}
```

`binaryStream()` 和 `BinaryStreamResource` 以同样的分块行为暴露内存中的二进制数据。PHP 字符串是二进制安全的，所以文件和流的分块都不意味着任何编码或行边界。

对于自定义的原生流来源，使用 `streamFromFactory()` 或 `StreamResource::fromFactory()`。工厂每次调用都必须返回一个新打开的 PHP 流；资源负责持有并关闭它。

## 错误

所有异常都实现 `Exception\FilesystemExceptionInterface`。路径、URI 或流描述为空或包含空字节，或分块大小小于 1 时，会在创建资源时抛出 `InvalidResourceConfigurationException`。来源无法打开或读取时，包括文件路径不是普通文件的情况，会在 `read()` 期间抛出 `ResourceReadException`。

## 契约边界

`ReadableResourceInterface<TValue>` 是泛型的，因为目录产出 `DirectoryEntry` 值，而面向字节的资源产出字符串。它们的共同行为是生命周期安全的读取，而不是强制统一的载荷表示。

本包是同步、只读的。它不提供写操作、异步或 Swoole 专用实现、路径包含策略、规范路径解析或 glob 匹配。
