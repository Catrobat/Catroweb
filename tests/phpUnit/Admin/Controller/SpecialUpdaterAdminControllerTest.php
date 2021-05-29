<?php

namespace Tests\phpUnit\Admin\Controller;

use App\Admin\Controller\SpecialUpdaterAdminController;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversNothing
 */
class SpecialUpdaterAdminControllerTest extends CatrowebTestCase
{
  /**
   * @var SpecialUpdaterAdminController|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SpecialUpdaterAdminController::class)
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
    $this->assertTrue(class_exists(SpecialUpdaterAdminController::class));
    $this->assertInstanceOf(SpecialUpdaterAdminController::class, $this->object);
  }
}
