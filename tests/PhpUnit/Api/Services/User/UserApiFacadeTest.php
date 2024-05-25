<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\User;

use App\Api\Services\Base\AbstractApiFacade;
use App\Api\Services\User\UserApiFacade;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\User\UserApiFacade
 */
final class UserApiFacadeTest extends DefaultTestCase
{
  protected MockObject|UserApiFacade $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserApiFacade::class)
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
    $this->assertTrue(class_exists(UserApiFacade::class));
    $this->assertInstanceOf(UserApiFacade::class, $this->object);
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
