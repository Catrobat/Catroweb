<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Admin\DB_Updater\Controller;

use App\Admin\DB_Updater\Controller\CronJobsAdminController;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
class CronJobAdminControllerTest extends DefaultTestCase
{
  protected CronJobsAdminController|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(CronJobsAdminController::class)
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
    $this->assertTrue(class_exists(CronJobsAdminController::class));
    $this->assertInstanceOf(CronJobsAdminController::class, $this->object);
  }
}
