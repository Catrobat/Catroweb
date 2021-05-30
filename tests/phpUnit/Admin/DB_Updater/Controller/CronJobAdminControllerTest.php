<?php

namespace Tests\phpUnit\Admin\DB_Updater\Controller;

use App\Admin\DB_Updater\Controller\CronJobsAdminController;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversNothing
 */
class CronJobAdminControllerTest extends CatrowebTestCase
{
  /**
   * @var CronJobsAdminController|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(CronJobsAdminController::class)
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
    $this->assertTrue(class_exists(CronJobsAdminController::class));
    $this->assertInstanceOf(CronJobsAdminController::class, $this->object);
  }
}
