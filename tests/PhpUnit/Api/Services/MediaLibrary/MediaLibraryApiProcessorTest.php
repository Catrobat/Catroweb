<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Api\Services\MediaLibrary\MediaLibraryApiProcessor;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\MediaLibrary\MediaLibraryApiProcessor
 */
final class MediaLibraryApiProcessorTest extends DefaultTestCase
{
  protected MediaLibraryApiProcessor|MockObject $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(MediaLibraryApiProcessor::class)
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
    $this->assertTrue(class_exists(MediaLibraryApiProcessor::class));
    $this->assertInstanceOf(MediaLibraryApiProcessor::class, $this->object);
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
