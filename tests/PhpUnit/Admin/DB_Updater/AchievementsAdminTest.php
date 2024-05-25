<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Admin\DB_Updater;

use App\Admin\DB_Updater\AchievementsAdmin;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\AdminBundle\Admin\AbstractAdmin;

/**
 * @internal
 *
 * @coversNothing
 */
class AchievementsAdminTest extends DefaultTestCase
{
  protected AchievementsAdmin|MockObject $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AchievementsAdmin::class)
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
    $this->assertTrue(class_exists(AchievementsAdmin::class));
    $this->assertInstanceOf(AchievementsAdmin::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractAdmin::class, $this->object);
  }
}
