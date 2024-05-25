<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\MediaLibrary\MediaLibraryResponseManager;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\MediaLibrary\MediaLibraryResponseManager
 */
final class MediaLibraryResponseManagerTest extends DefaultTestCase
{
  protected MediaLibraryResponseManager|MockObject $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(MediaLibraryResponseManager::class)
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
    $this->assertTrue(class_exists(MediaLibraryResponseManager::class));
    $this->assertInstanceOf(MediaLibraryResponseManager::class, $this->object);
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
