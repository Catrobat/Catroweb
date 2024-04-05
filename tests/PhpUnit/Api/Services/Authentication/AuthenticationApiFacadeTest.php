<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Authentication;

use App\Api\Services\Authentication\AuthenticationApiFacade;
use App\Api\Services\Base\AbstractApiFacade;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Authentication\AuthenticationApiFacade
 */
final class AuthenticationApiFacadeTest extends DefaultTestCase
{
  protected AuthenticationApiFacade|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AuthenticationApiFacade::class)
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
    $this->assertTrue(class_exists(AuthenticationApiFacade::class));
    $this->assertInstanceOf(AuthenticationApiFacade::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiFacade::class, $this->object);
  }
}
