<?php

namespace Tests\PhpUnit\Api\Services\Search;

use App\Api\Services\Base\AbstractApiFacade;
use App\Api\Services\Search\SearchApiFacade;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Search\SearchApiFacade
 */
final class SearchApiFacadeTest extends DefaultTestCase
{
  protected SearchApiFacade|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SearchApiFacade::class)
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
    $this->assertTrue(class_exists(SearchApiFacade::class));
    $this->assertInstanceOf(SearchApiFacade::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiFacade::class, $this->object);
  }
}
