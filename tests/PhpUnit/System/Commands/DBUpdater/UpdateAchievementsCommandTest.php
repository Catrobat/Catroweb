<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater;

use App\System\Commands\DBUpdater\UpdateAchievementsCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Class UpdateAchievementsCommand.
 *
 * @internal
 *
 * @covers \App\System\Commands\DBUpdater\UpdateAchievementsCommand
 */
class UpdateAchievementsCommandTest extends KernelTestCase
{
  protected MockObject|UpdateAchievementsCommand $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UpdateAchievementsCommand::class)
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
    $this->assertTrue(class_exists(UpdateAchievementsCommand::class));
    $this->assertInstanceOf(UpdateAchievementsCommand::class, $this->object);
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
