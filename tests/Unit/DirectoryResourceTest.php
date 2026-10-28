<?php

declare(strict_types=1);

namespace SuperKernel\Filesystem\Tests\Unit;

use SuperKernel\Filesystem\Enum\DirectoryEntryType;
use SuperKernel\Filesystem\Exception\ResourceReadException;
use SuperKernel\Filesystem\Model\DirectoryEntry;
use SuperKernel\Filesystem\Resource\DirectoryResource;
use SuperKernel\Filesystem\Tests\Support\TestCase;
use Throwable;

use function array_keys;
use function iterator_to_array;
use function ksort;
use function mkdir;

final class DirectoryResourceTest extends TestCase
{
    /** @throws Throwable When fixture setup or the operation under test throws */
    public function testReadsTypedDirectoryEntriesWithoutDotEntries(): void
    {
        $this->withTemporaryDirectory(function (
            string $directory,
        ): void {
            $this->writeFile($directory . '/file.txt', 'content');
            $this->assertTrue(mkdir($directory . '/nested'));

            $entries = [];

            foreach (new DirectoryResource($directory)->read() as $entry) {
                $entries[$entry->name()] = $entry;
            }

            ksort($entries);

            $this->assertSame([
                'file.txt',
                'nested',
            ], array_keys($entries));
            $this->assertContainsOnlyInstancesOf(DirectoryEntry::class, $entries);
            $this->assertSame(DirectoryEntryType::File, $entries['file.txt']->type());
            $this->assertSame(DirectoryEntryType::Directory, $entries['nested']->type());
            $this->assertSame($directory . '/file.txt', $entries['file.txt']->path());
            $this->assertCount(2, iterator_to_array(new DirectoryResource($directory)->read()));
        });
    }

    public function testMissingDirectoryFailsOnlyWhenReadingBegins(): void
    {
        $resource = new DirectoryResource('/definitely/missing/super-kernel-directory');
        $readCycle = $resource->read();

        $this->assertThrows(
            ResourceReadException::class,
            static fn(): array => iterator_to_array($readCycle),
            'Unable to open directory',
        );
    }
}
