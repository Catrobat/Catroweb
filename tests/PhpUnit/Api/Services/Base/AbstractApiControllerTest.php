<?php

namespace Tests\PhpUnit\Api\Services\Base;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Base\AbstractApiFacade;
use App\Api\Services\Base\PandaAuthenticationInterface;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Base\AbstractApiController
 */
final class AbstractApiControllerTest extends DefaultTestCase
{
  protected AbstractApiFacade|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AbstractApiController::class)
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
    $this->assertTrue(class_exists(AbstractApiController::class));
    $this->assertInstanceOf(AbstractApiController::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractController::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(PandaAuthenticationInterface::class, $this->object);
  }
}
