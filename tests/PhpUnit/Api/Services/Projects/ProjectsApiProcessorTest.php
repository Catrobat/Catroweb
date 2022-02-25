<?php

namespace Tests\PhpUnit\Api\Services\Projects;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Api\Services\Projects\ProjectsApiProcessor;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Projects\ProjectsApiProcessor
 */
final class ProjectsApiProcessorTest extends DefaultTestCase
{
  /**
   * @var ProjectsApiProcessor|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProjectsApiProcessor::class)
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
    $this->assertTrue(class_exists(ProjectsApiProcessor::class));
    $this->assertInstanceOf(ProjectsApiProcessor::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiProcessor::class, $this->object);
  }
}
