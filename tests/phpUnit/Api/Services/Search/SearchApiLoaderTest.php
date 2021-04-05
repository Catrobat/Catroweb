<?php

namespace Tests\phpUnit\Api\Services\Search;

use App\Api\Services\Base\AbstractApiLoader;
use App\Api\Services\Search\SearchApiLoader;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Search\SearchApiLoader
 */
final class SearchApiLoaderTest extends CatrowebTestCase
{
  /**
   * @var SearchApiLoader|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SearchApiLoader::class)
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
    $this->assertTrue(class_exists(SearchApiLoader::class));
    $this->assertInstanceOf(SearchApiLoader::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiLoader::class, $this->object);
  }
}
