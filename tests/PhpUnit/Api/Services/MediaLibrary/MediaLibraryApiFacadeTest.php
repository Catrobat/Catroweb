<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractApiFacade;
use App\Api\Services\MediaLibrary\MediaLibraryApiFacade;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\MediaLibrary\MediaLibraryApiFacade
 */
final class MediaLibraryApiFacadeTest extends DefaultTestCase
{
  protected MediaLibraryApiFacade|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(MediaLibraryApiFacade::class)
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
    $this->assertTrue(class_exists(MediaLibraryApiFacade::class));
    $this->assertInstanceOf(MediaLibraryApiFacade::class, $this->object);
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
