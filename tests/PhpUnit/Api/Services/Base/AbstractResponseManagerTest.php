<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Base;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Base\TranslatorAwareInterface;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Service\JmsSerializer;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Base\AbstractResponseManager
 */
final class AbstractResponseManagerTest extends DefaultTestCase
{
  protected AbstractResponseManager|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AbstractResponseManager::class)
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
    $this->assertTrue(class_exists(AbstractResponseManager::class));
    $this->assertInstanceOf(AbstractResponseManager::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractResponseManager::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(TranslatorAwareInterface::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   *
   * @throws \ReflectionException
   */
  public function testAddResponseHashToHeaders(): void
  {
    $this->mockProperty(AbstractResponseManager::class, $this->object, 'serializer', new JmsSerializer());

    $responseHeaders = [];
    $response = new ProjectResponse(['id' => '1']);
    $this->object->addResponseHashToHeaders($responseHeaders, $response);
    $hash_1 = $responseHeaders['X-Response-Hash'];

    $responseHeaders = [];
    $response = new ProjectResponse(['id' => '1']);
    $this->object->addResponseHashToHeaders($responseHeaders, $response);
    $hash_2 = $responseHeaders['X-Response-Hash'];

    $response = new ProjectResponse(['id' => '2']);
    $this->object->addResponseHashToHeaders($responseHeaders, $response);
    $hash_3 = $responseHeaders['X-Response-Hash'];

    $this->assertSame($hash_1, $hash_2);
    $this->assertNotEquals($hash_1, $hash_3);
  }
}
