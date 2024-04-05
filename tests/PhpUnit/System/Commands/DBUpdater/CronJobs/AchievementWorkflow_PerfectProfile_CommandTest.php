<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater\CronJobs;

use App\System\Commands\DBUpdater\CronJobs\AchievementWorkflow_PerfectProfile_Command;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Class AchievementWorkflow_PerfectProfile_Command.
 *
 * @internal
 *
 * @covers \App\System\Commands\DBUpdater\CronJobs\AchievementWorkflow_PerfectProfile_Command
 */
class AchievementWorkflow_PerfectProfile_CommandTest extends KernelTestCase
{
  protected AchievementWorkflow_PerfectProfile_Command|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AchievementWorkflow_PerfectProfile_Command::class)
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
    $this->assertTrue(class_exists(AchievementWorkflow_PerfectProfile_Command::class));
    $this->assertInstanceOf(AchievementWorkflow_PerfectProfile_Command::class, $this->object);
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
