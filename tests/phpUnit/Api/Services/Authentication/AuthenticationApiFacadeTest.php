<?php

namespace Tests\phpUnit\Api\Services\Authentication;

use App\Api\Services\Authentication\AuthenticationApiFacade;
use App\Api\Services\Base\AbstractApiFacade;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Authentication\AuthenticationApiFacade
 */
final class AuthenticationApiFacadeTest extends CatrowebTestCase
{
  /**
   * @var AuthenticationApiFacade|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AuthenticationApiFacade::class)
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
    $this->assertTrue(class_exists(AuthenticationApiFacade::class));
    $this->assertInstanceOf(AuthenticationApiFacade::class, $this->object);
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
