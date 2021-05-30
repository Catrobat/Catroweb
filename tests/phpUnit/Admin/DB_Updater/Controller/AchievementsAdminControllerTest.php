<?php

namespace Tests\phpUnit\Admin\DB_Updater\Controller;

use App\Admin\DB_Updater\Controller\AchievementsAdminController;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversNothing
 */
class AchievementsAdminControllerTest extends CatrowebTestCase
{
  /**
   * @var AchievementsAdminController|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AchievementsAdminController::class)
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
    $this->assertTrue(class_exists(AchievementsAdminController::class));
    $this->assertInstanceOf(AchievementsAdminController::class, $this->object);
  }
}
