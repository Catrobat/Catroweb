<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Base;

use App\Api\Services\Base\AbstractResponseManager;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Service\JmsSerializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(AbstractResponseManager::class)]
final class AbstractResponseManagerTest extends DefaultTestCase
{
  protected AbstractResponseManagerTestClass $object;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $translator = $this->createStub(TranslatorInterface::class);
    $serializer = new JmsSerializer();
    $cache = $this->createStub(CacheItemPoolInterface::class);

    $this->object = new AbstractResponseManagerTestClass($translator, $serializer, $cache);
  }

  #[Group('integration')]
  public function testAddResponseHashToHeaders(): void
  {
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
