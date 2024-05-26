<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater;

use App\System\Commands\DBUpdater\SpecialUpdateCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Class SpecialUpdateCommand.
 *
 * @internal
 *
 * @covers \App\System\Commands\DBUpdater\SpecialUpdateCommand
 */
class SpecialUpdateCommandTest extends KernelTestCase
{
  protected MockObject|SpecialUpdateCommand $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SpecialUpdateCommand::class)
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
    $this->assertTrue(class_exists(SpecialUpdateCommand::class));
    $this->assertInstanceOf(SpecialUpdateCommand::class, $this->object);
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
