<?php

namespace Tests\phpUnit\Api\Services\Search;

use App\Api\Services\Base\AbstractApiFacade;
use App\Api\Services\Search\SearchApiFacade;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Search\SearchApiFacade
 */
final class SearchApiFacadeTest extends CatrowebTestCase
{
  /**
   * @var SearchApiFacade|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SearchApiFacade::class)
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
    $this->assertTrue(class_exists(SearchApiFacade::class));
    $this->assertInstanceOf(SearchApiFacade::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiFacade::class, $this->object);
  }
}
