<?php

namespace Tests\phpUnit\Api\Services\User;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Api\Services\User\UserApiProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\User\UserApiProcessor
 */
final class UserApiProcessorTest extends CatrowebTestCase
{
  /**
   * @var UserApiProcessor|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserApiProcessor::class)
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
    $this->assertTrue(class_exists(UserApiProcessor::class));
    $this->assertInstanceOf(UserApiProcessor::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiProcessor::class, $this->object);
  }
}
