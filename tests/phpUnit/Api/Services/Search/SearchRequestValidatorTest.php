<?php

namespace Tests\phpUnit\Api\Services\Search;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\Search\SearchRequestValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Search\SearchRequestValidator
 */
final class SearchRequestValidatorTest extends CatrowebTestCase
{
  /**
   * @var SearchRequestValidator|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SearchRequestValidator::class)
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
    $this->assertTrue(class_exists(SearchRequestValidator::class));
    $this->assertInstanceOf(SearchRequestValidator::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractRequestValidator::class, $this->object);
  }
}
