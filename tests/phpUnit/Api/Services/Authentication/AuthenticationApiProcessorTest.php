<?php

namespace Tests\phpUnit\Api\Services\Authentication;

use App\Api\Services\Authentication\AuthenticationApiProcessor;
use App\Api\Services\Base\AbstractApiProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Authentication\AuthenticationApiProcessor
 */
final class AuthenticationApiProcessorTest extends CatrowebTestCase
{
  /**
   * @var AuthenticationApiProcessor|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AuthenticationApiProcessor::class)
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
    $this->assertTrue(class_exists(AuthenticationApiProcessor::class));
    $this->assertInstanceOf(AuthenticationApiProcessor::class, $this->object);
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
