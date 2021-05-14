<?php

declare(strict_types=1);

namespace Tests\phpUnit\Repository\Achievements;

use App\Repository\Achievements\UserAchievementRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Repository\Achievements\UserAchievementRepository
 */
final class UserAchievementRepositoryTest extends CatrowebTestCase
{
  /**
   * @var UserAchievementRepository|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserAchievementRepository::class)
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
    $this->assertTrue(class_exists(UserAchievementRepository::class));
    $this->assertInstanceOf(UserAchievementRepository::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(ServiceEntityRepository::class, $this->object);
  }
}
