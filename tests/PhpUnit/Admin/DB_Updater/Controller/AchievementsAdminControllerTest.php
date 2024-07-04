<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Admin\DB_Updater\Controller;

use App\Admin\System\DB_Updater\Controller\AchievementsAdminController;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
class AchievementsAdminControllerTest extends DefaultTestCase
{
  protected AchievementsAdminController|MockObject $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AchievementsAdminController::class)
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
    $this->assertTrue(class_exists(AchievementsAdminController::class));
    $this->assertInstanceOf(AchievementsAdminController::class, $this->object);
  }
}
