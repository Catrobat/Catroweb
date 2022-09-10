<?php

namespace Tests\PhpUnit\Admin\DB_Updater\Controller;

use App\Admin\DB_Updater\Controller\SpecialUpdaterAdminController;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
class SpecialUpdaterAdminControllerTest extends DefaultTestCase
{
  protected SpecialUpdaterAdminController|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SpecialUpdaterAdminController::class)
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
    $this->assertTrue(class_exists(SpecialUpdaterAdminController::class));
    $this->assertInstanceOf(SpecialUpdaterAdminController::class, $this->object);
  }
}
