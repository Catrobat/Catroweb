<?php

namespace Tests\phpUnit\Api\Services\Search;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Api\Services\Search\SearchApiProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Search\SearchApiProcessor
 */
final class SearchApiProcessorTest extends CatrowebTestCase
{
  /**
   * @var SearchApiProcessor|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SearchApiProcessor::class)
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
    $this->assertTrue(class_exists(SearchApiProcessor::class));
    $this->assertInstanceOf(SearchApiProcessor::class, $this->object);
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
