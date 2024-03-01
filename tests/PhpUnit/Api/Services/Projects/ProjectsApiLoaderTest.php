<?php

namespace Tests\PhpUnit\Api\Services\Projects;

use App\Api\Services\Base\AbstractApiLoader;
use App\Api\Services\Projects\ProjectsApiLoader;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Projects\ProjectsApiLoader
 */
final class ProjectsApiLoaderTest extends DefaultTestCase
{
  protected MockObject|ProjectsApiLoader $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProjectsApiLoader::class)
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
    $this->assertTrue(class_exists(ProjectsApiLoader::class));
    $this->assertInstanceOf(ProjectsApiLoader::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiLoader::class, $this->object);
  }
}
