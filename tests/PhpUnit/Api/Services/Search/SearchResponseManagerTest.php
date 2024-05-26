<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Search;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Search\SearchResponseManager;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Search\SearchResponseManager
 */
final class SearchResponseManagerTest extends DefaultTestCase
{
  protected MockObject|SearchResponseManager $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(SearchResponseManager::class)
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
    $this->assertTrue(class_exists(SearchResponseManager::class));
    $this->assertInstanceOf(SearchResponseManager::class, $this->object);
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
}
