<?php

namespace Tests\phpUnit\Commands\DBUpdater\CronJobs;

use App\Commands\DBUpdater\CronJobs\UpdateRandomProjectCategoryCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Class UpdateRandomProjectCategoryCommandTest.
 *
 * @internal
 * @covers \App\Commands\DBUpdater\CronJobs\UpdateRandomProjectCategoryCommand
 */
class UpdateRandomProjectCategoryCommandTest extends KernelTestCase
{
  /**
   * @var UpdateRandomProjectCategoryCommand|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UpdateRandomProjectCategoryCommand::class)
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
    $this->assertTrue(class_exists(UpdateRandomProjectCategoryCommand::class));
    $this->assertInstanceOf(UpdateRandomProjectCategoryCommand::class, $this->object);
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
