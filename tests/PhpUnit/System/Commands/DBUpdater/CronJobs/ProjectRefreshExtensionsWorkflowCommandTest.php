<?php

namespace Tests\PhpUnit\System\Commands\DBUpdater\CronJobs;

use App\System\Commands\DBUpdater\CronJobs\ProjectRefreshExtensionsWorkflowCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Class ProjectRefreshExtensionsWorkflowCommand.
 *
 * @internal
 * @covers \App\System\Commands\DBUpdater\CronJobs\ProjectRefreshExtensionsWorkflowCommand
 */
class ProjectRefreshExtensionsWorkflowCommandTest extends KernelTestCase
{
  /**
   * @var ProjectRefreshExtensionsWorkflowCommand|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProjectRefreshExtensionsWorkflowCommand::class)
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
    $this->assertTrue(class_exists(ProjectRefreshExtensionsWorkflowCommand::class));
    $this->assertInstanceOf(ProjectRefreshExtensionsWorkflowCommand::class, $this->object);
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
