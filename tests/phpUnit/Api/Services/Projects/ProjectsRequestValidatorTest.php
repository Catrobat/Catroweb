<?php

namespace Tests\phpUnit\Api\Services\Projects;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\Projects\ProjectsRequestValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Projects\ProjectsRequestValidator
 */
final class ProjectsRequestValidatorTest extends CatrowebTestCase
{
  /**
   * @var ProjectsRequestValidator|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProjectsRequestValidator::class)
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
    $this->assertTrue(class_exists(ProjectsRequestValidator::class));
    $this->assertInstanceOf(ProjectsRequestValidator::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractRequestValidator::class, $this->object);
  }
}
