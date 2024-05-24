<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Authentication;

use App\Api\Services\Authentication\AuthenticationApiLoader;
use App\Api\Services\Base\AbstractApiLoader;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Authentication\AuthenticationApiLoader
 */
final class AuthenticationApiLoaderTest extends DefaultTestCase
{
  protected AuthenticationApiLoader|MockObject $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AuthenticationApiLoader::class)
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
    $this->assertTrue(class_exists(AuthenticationApiLoader::class));
    $this->assertInstanceOf(AuthenticationApiLoader::class, $this->object);
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
