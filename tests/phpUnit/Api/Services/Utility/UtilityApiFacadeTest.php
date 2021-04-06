<?php

namespace Tests\phpUnit\Api\Services\Utility;

use App\Api\Services\Base\AbstractApiFacade;
use App\Api\Services\Utility\UtilityApiFacade;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Utility\UtilityApiFacade
 */
final class UtilityApiFacadeTest extends CatrowebTestCase
{
  /**
   * @var UtilityApiFacade|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UtilityApiFacade::class)
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
    $this->assertTrue(class_exists(UtilityApiFacade::class));
    $this->assertInstanceOf(UtilityApiFacade::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiFacade::class, $this->object);
  }
}
