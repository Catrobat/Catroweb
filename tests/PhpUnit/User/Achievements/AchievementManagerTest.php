<?php

declare(strict_types=1);

namespace Tests\PhpUnit\User\Achievements;

use App\System\Testing\PhpUnit\DefaultTestCase;
use App\User\Achievements\AchievementManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\User\Achievements\AchievementManager
 */
final class AchievementManagerTest extends DefaultTestCase
{
  protected AchievementManager|MockObject $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AchievementManager::class)
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
    $this->assertTrue(class_exists(AchievementManager::class));
    $this->assertInstanceOf(AchievementManager::class, $this->object);
  }
}
