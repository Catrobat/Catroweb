<?php

namespace Tests\phpUnit\Api\Services\Utility;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Utility\UtilityResponseManager;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Utility\UtilityResponseManager
 */
final class UtilityResponseManagerTest extends CatrowebTestCase
{
  /**
   * @var UtilityResponseManager|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UtilityResponseManager::class)
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
    $this->assertTrue(class_exists(UtilityResponseManager::class));
    $this->assertInstanceOf(UtilityResponseManager::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractResponseManager::class, $this->object);
  }
}
