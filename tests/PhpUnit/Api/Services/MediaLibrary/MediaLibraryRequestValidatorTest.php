<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\MediaLibrary\MediaLibraryRequestValidator;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\MediaLibrary\MediaLibraryRequestValidator
 */
final class MediaLibraryRequestValidatorTest extends DefaultTestCase
{
  protected MediaLibraryRequestValidator|MockObject $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(MediaLibraryRequestValidator::class)
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
    $this->assertTrue(class_exists(MediaLibraryRequestValidator::class));
    $this->assertInstanceOf(MediaLibraryRequestValidator::class, $this->object);
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
