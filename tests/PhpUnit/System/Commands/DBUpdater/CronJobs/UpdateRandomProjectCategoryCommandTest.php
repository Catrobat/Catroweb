<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater\CronJobs;

use App\System\Commands\DBUpdater\CronJobs\UpdateRandomProjectCategoryCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Class UpdateRandomProjectCategoryCommandTest.
 *
 * @internal
 *
 * @covers \App\System\Commands\DBUpdater\CronJobs\UpdateRandomProjectCategoryCommand
 */
class UpdateRandomProjectCategoryCommandTest extends KernelTestCase
{
  protected MockObject|UpdateRandomProjectCategoryCommand $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UpdateRandomProjectCategoryCommand::class)
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
    $this->assertTrue(class_exists(UpdateRandomProjectCategoryCommand::class));
    $this->assertInstanceOf(UpdateRandomProjectCategoryCommand::class, $this->object);
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
