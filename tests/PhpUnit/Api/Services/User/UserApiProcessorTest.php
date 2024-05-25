<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\User;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Api\Services\User\UserApiProcessor;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\User\UserApiProcessor
 */
final class UserApiProcessorTest extends DefaultTestCase
{
  protected MockObject|UserApiProcessor $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserApiProcessor::class)
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
    $this->assertTrue(class_exists(UserApiProcessor::class));
    $this->assertInstanceOf(UserApiProcessor::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiProcessor::class, $this->object);
  }
}
