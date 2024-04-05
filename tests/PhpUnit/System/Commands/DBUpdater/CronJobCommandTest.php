<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater;

use App\System\Commands\DBUpdater\CronJobCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Class CronJobCommand.
 *
 * @internal
 *
 * @covers \App\System\Commands\DBUpdater\CronJobCommand
 */
class CronJobCommandTest extends KernelTestCase
{
  protected CronJobCommand|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(CronJobCommand::class)
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
    $this->assertTrue(class_exists(CronJobCommand::class));
    $this->assertInstanceOf(CronJobCommand::class, $this->object);
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
