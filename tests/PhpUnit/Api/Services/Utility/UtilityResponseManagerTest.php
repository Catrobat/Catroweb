<?php

namespace Tests\PhpUnit\Api\Services\Utility;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Utility\UtilityResponseManager;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Utility\UtilityResponseManager
 */
final class UtilityResponseManagerTest extends DefaultTestCase
{
  protected MockObject|UtilityResponseManager $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UtilityResponseManager::class)
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
    $this->assertTrue(class_exists(UtilityResponseManager::class));
    $this->assertInstanceOf(UtilityResponseManager::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractResponseManager::class, $this->object);
  }
}
