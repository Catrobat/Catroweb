<?php

namespace Tests\phpUnit\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\MediaLibrary\MediaLibraryResponseManager;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\MediaLibrary\MediaLibraryResponseManager
 */
final class MediaLibraryResponseManagerTest extends CatrowebTestCase
{
  /**
   * @var MediaLibraryResponseManager|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(MediaLibraryResponseManager::class)
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
    $this->assertTrue(class_exists(MediaLibraryResponseManager::class));
    $this->assertInstanceOf(MediaLibraryResponseManager::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractResponseManager::class, $this->object);
  }
}
