<?php

namespace Tests\phpUnit\Commands\DBUpdater;

use App\Commands\DBUpdater\UpdateProjectExtensionsCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Class UpdateProjectExtensionsCommand.
 *
 * @internal
 * @covers \App\Commands\DBUpdater\UpdateProjectExtensionsCommand
 */
class UpdateProjectExtensionsCommandTest extends KernelTestCase
{
  /**
   * @var UpdateProjectExtensionsCommand|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UpdateProjectExtensionsCommand::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
        ;
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(UpdateProjectExtensionsCommand::class));
    $this->assertInstanceOf(UpdateProjectExtensionsCommand::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(Command::class, $this->object);
  }
}
