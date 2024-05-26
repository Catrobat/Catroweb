<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Projects;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Projects\ProjectsResponseManager;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Projects\ProjectsResponseManager
 */
final class ProjectsResponseManagerTest extends DefaultTestCase
{
  protected MockObject|ProjectsResponseManager $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProjectsResponseManager::class)
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
    $this->assertTrue(class_exists(ProjectsResponseManager::class));
    $this->assertInstanceOf(ProjectsResponseManager::class, $this->object);
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
