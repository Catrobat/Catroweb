<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater\CronJobs;

use App\System\Commands\DBUpdater\CronJobs\TranslationTrimStorageCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

/**
 * @internal
 *
 * @covers \App\System\Commands\DBUpdater\CronJobs\TranslationTrimStorageCommand
 */
class TranslationTrimStorageCommandTest extends TestCase
{
  protected MockObject|TranslationTrimStorageCommand $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(TranslationTrimStorageCommand::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(TranslationTrimStorageCommand::class));
    $this->assertInstanceOf(TranslationTrimStorageCommand::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(Command::class, $this->object);
  }
}
