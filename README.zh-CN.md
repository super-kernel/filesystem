<p align="right">
  <a href="README.md">English</a> | <strong>中文文档</strong>
</p>

# SuperKernel Filesystem

`super-kernel/filesystem` 通过一个方法 `read()` 读取文件、目录和流，由该方法自己打开和关闭 PHP 句柄，因此读取的代码从不持有或关闭句柄。

## 安装

```bash
composer require super-kernel/filesystem
```

本包除 PHP 外没有其他要求。它的类都是普通对象：不需要容器，也不需要构建。

## 提供的能力

- 文件、流和内存资源，按选定的大小产出二进制安全的数据块。
- 目录资源，产出直接子条目及其名称、路径和类型。
- 每次 `read()` 都是一个新的读取周期，在迭代开始时打开，在迭代结束、失败或被放弃时关闭。
- 来自 PHP URI 或你自己的工厂的流。
- `ResourceFactoryInterface`，使代码可以依赖契约而不是具体类。

它不会：

- 写入、移动或删除文件；
- 异步读取或提供 Swoole 专用的资源；
- 限制、规范化或展开路径（例如用 glob 模式）；
- 注册容器服务。

## 快速开始

以八个字节为一块读取文件。将脚本保存为 `bin/read-file.php`：

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

运行脚本：

```bash
php bin/read-file.php
```

它会打印：

```text
[INV-1001]
[\nINV-100]
[2\n]
```

## 公开接口

| 名称 | 类别 | 用途 |
| --- | --- | --- |
| `SuperKernel\Filesystem\Contract\ResourceFactoryInterface` | 接口 | 创建目录、文件、流、工厂流和内存资源。 |
| `SuperKernel\Filesystem\Factory\LocalResourceFactory` | 类 | 面向本地文件和 PHP 流的 `ResourceFactoryInterface` 实现。 |
| `SuperKernel\Filesystem\Contract\ReadableResourceInterface` | 接口 | `read()` 开始一个新的读取周期并产出资源的值。 |
| `SuperKernel\Filesystem\Resource\FileResource` | 类 | 按块读取普通文件。 |
| `SuperKernel\Filesystem\Resource\DirectoryResource` | 类 | 读取目录的直接子条目。 |
| `SuperKernel\Filesystem\Resource\StreamResource` | 类 | 读取用 `fromUri()` 从 URI 打开、或用 `fromFactory()` 由工厂打开的流。 |
| `SuperKernel\Filesystem\Resource\BinaryStreamResource` | 类 | 按块读取保存在内存中的字符串。 |
| `SuperKernel\Filesystem\Model\DirectoryEntry` | 类 | 一个目录条目：`name()`、`path()` 和 `type()`。 |
| `SuperKernel\Filesystem\Constants\DirectoryEntryType` | 枚举 | 条目类型：`directory`、`file`、`link` 或 `other`。 |

## 需要了解的行为

- 数据块是长度不超过块大小（默认 8192 字节）的字符串；它不意味着任何编码或行边界。
- 目录条目按文件系统自己的、未指定的顺序出现，不包括 `.` 和 `..`。
- 每个异常都实现 `SuperKernel\Filesystem\Exception\FilesystemExceptionInterface`。空位置、包含空字节的位置或小于一的块大小，会在创建资源时抛出 `InvalidResourceConfigurationException`，例如 `Chunk size must be greater than zero.`
- 无法打开或读取的来源（包括不是普通文件的文件路径）会在 `read()` 期间抛出 `ResourceReadException`，例如 `File "<path>" is not a regular file.`
- 传给 `streamFromFactory()` 的工厂每次调用都必须返回一个新打开的流；资源在每次读取之后关闭它。

## 文档

开发者文档介绍了如何读取文件、目录、流和内存、一次读取的生命周期，以及每条错误消息，并附有完整示例：[SuperKernel Filesystem](https://github.com/super-kernel/.github/blob/main/docs/components/filesystem.zh-CN.md)。

## 许可证

基于 MIT 许可证发布，详见 [LICENSE](LICENSE)。
