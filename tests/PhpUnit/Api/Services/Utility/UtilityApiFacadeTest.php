<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Utility;

use App\Api\Services\Base\AbstractApiFacade;
use App\Api\Services\Utility\UtilityApiFacade;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Utility\UtilityApiFacade
 */
final class UtilityApiFacadeTest extends DefaultTestCase
{
  protected MockObject|UtilityApiFacade $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UtilityApiFacade::class)
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
    $this->assertTrue(class_exists(UtilityApiFacade::class));
    $this->assertInstanceOf(UtilityApiFacade::class, $this->object);
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
