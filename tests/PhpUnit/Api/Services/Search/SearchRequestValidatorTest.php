<?php

namespace Tests\PhpUnit\Api\Services\Search;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\Search\SearchRequestValidator;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Search\SearchRequestValidator
 */
final class SearchRequestValidatorTest extends DefaultTestCase
{
  protected MockObject|SearchRequestValidator $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SearchRequestValidator::class)
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
    $this->assertTrue(class_exists(SearchRequestValidator::class));
    $this->assertInstanceOf(SearchRequestValidator::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractRequestValidator::class, $this->object);
  }
}
