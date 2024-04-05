<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Admin\Tools\Maintenance;

use App\Admin\Tools\Maintenance\RemovableMemory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Admin\Tools\Maintenance\RemovableMemory
 *
 * @internal
 */
class RemovableMemoryTest extends TestCase
{
  private RemovableMemory $removable_memory;

  protected function setUp(): void
  {
    $this->removable_memory = new RemovableMemory();
  }

  public function testRemovableMemory(): void
  {
    $this->assertInstanceOf(RemovableMemory::class, $this->removable_memory);
    $this->removable_memory->setCommandLink('test');
    $this->assertSame('test', $this->removable_memory->command_link);
    $this->removable_memory->setCommandName('test');
    $this->assertSame('test', $this->removable_memory->command_name);
    $this->removable_memory->setExecuteLink('test');
    $this->assertSame('test', $this->removable_memory->execute_link);
    $this->removable_memory->setDownloadLink('test');
    $this->assertSame('test', $this->removable_memory->download_link);
    $this->removable_memory->setSize('3.24KiB');
    $this->assertSame('3.24KiB', $this->removable_memory->size);
    $this->removable_memory->setSizeRaw(64);
    $this->assertSame(64, $this->removable_memory->size_raw);
    $this->removable_memory->setArchiveCommandLink('test');
    $this->assertSame('test', $this->removable_memory->getArchiveCommandLink());
    $this->removable_memory->setArchiveCommandName('test');
    $this->assertSame('test', $this->removable_memory->getArchiveCommandName());
  }
}
