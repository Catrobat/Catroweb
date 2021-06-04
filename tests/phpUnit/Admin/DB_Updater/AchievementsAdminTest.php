<?php

namespace Tests\phpUnit\Admin\DB_Updater;

use App\Admin\DB_Updater\AchievementsAdmin;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversNothing
 */
class AchievementsAdminTest extends CatrowebTestCase
{
  /**
   * @var AchievementsAdmin|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AchievementsAdmin::class)
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
    $this->assertTrue(class_exists(AchievementsAdmin::class));
    $this->assertInstanceOf(AchievementsAdmin::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractAdmin::class, $this->object);
  }
}
