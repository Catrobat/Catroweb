<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\User;

use App\Api\Services\Base\AbstractApiLoader;
use App\Api\Services\User\UserApiLoader;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\User\UserApiLoader
 */
final class UserApiLoaderTest extends DefaultTestCase
{
  protected MockObject|UserApiLoader $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserApiLoader::class)
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
    $this->assertTrue(class_exists(UserApiLoader::class));
    $this->assertInstanceOf(UserApiLoader::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiLoader::class, $this->object);
  }
}
