<?php

namespace Tests\phpUnit\Api\Services\Search;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Search\SearchResponseManager;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Search\SearchResponseManager
 */
final class SearchResponseManagerTest extends CatrowebTestCase
{
  /**
   * @var SearchResponseManager|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SearchResponseManager::class)
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
    $this->assertTrue(class_exists(SearchResponseManager::class));
    $this->assertInstanceOf(SearchResponseManager::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractResponseManager::class, $this->object);
  }
}
