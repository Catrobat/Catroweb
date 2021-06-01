<?php

namespace Tests\phpUnit\Admin\DB_Updater;

use App\Admin\DB_Updater\SpecialUpdaterAdmin;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversNothing
 */
class SpecialUpdaterAdminTest extends CatrowebTestCase
{
  /**
   * @var SpecialUpdaterAdmin|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SpecialUpdaterAdmin::class)
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
    $this->assertTrue(class_exists(SpecialUpdaterAdmin::class));
    $this->assertInstanceOf(SpecialUpdaterAdmin::class, $this->object);
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
