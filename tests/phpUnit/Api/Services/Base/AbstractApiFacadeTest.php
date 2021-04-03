<?php

namespace Tests\phpUnit\Api\Services\Base;

use App\Api\Services\Base\AbstractApiFacade;
use App\Api\Services\Base\ApiFacadeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Base\AbstractApiFacade
 */
final class AbstractApiFacadeTest extends CatrowebTestCase
{
  /**
   * @var AbstractApiFacade|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AbstractApiFacade::class)
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
    $this->assertTrue(class_exists(AbstractApiFacade::class));
    $this->assertInstanceOf(AbstractApiFacade::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(ApiFacadeInterface::class, $this->object);
  }
}
