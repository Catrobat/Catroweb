<?php

namespace Tests\phpUnit\Commands\DBUpdater;

use App\Commands\DBUpdater\UpdateAchievementsCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Class ClearCompressedProjectsTest.
 *
 * @internal
 * @covers \App\Commands\Maintenance\CleanCompressedProjectsCommand
 */
class UpdateAchievementsCommandTest extends KernelTestCase
{
  /**
   * @var UpdateAchievementsCommand|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UpdateAchievementsCommand::class)
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
    $this->assertTrue(class_exists(UpdateAchievementsCommand::class));
    $this->assertInstanceOf(UpdateAchievementsCommand::class, $this->object);
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
