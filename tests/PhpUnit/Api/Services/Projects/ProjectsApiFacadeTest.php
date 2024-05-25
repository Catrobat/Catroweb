<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Projects;

use App\Api\Services\Base\AbstractApiFacade;
use App\Api\Services\Projects\ProjectsApiFacade;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Projects\ProjectsApiFacade
 */
final class ProjectsApiFacadeTest extends DefaultTestCase
{
  protected MockObject|ProjectsApiFacade $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProjectsApiFacade::class)
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
    $this->assertTrue(class_exists(ProjectsApiFacade::class));
    $this->assertInstanceOf(ProjectsApiFacade::class, $this->object);
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
