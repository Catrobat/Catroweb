<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Search;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Api\Services\Search\SearchApiProcessor;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Search\SearchApiProcessor
 */
final class SearchApiProcessorTest extends DefaultTestCase
{
  protected MockObject|SearchApiProcessor $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SearchApiProcessor::class)
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
    $this->assertTrue(class_exists(SearchApiProcessor::class));
    $this->assertInstanceOf(SearchApiProcessor::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiProcessor::class, $this->object);
  }
}
