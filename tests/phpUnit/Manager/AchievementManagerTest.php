<?php

declare(strict_types=1);

namespace Tests\phpUnit\Manager;

use App\Manager\AchievementManager;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Manager\AchievementManager
 */
final class AchievementManagerTest extends CatrowebTestCase
{
  /**
   * @var AchievementManager|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AchievementManager::class)
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
    $this->assertTrue(class_exists(AchievementManager::class));
    $this->assertInstanceOf(AchievementManager::class, $this->object);
  }
}
