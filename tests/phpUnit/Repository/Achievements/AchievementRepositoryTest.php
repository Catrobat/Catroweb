<?php

declare(strict_types=1);

namespace Tests\phpUnit\Repository\Achievements;

use App\Repository\Achievements\AchievementRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Repository\Achievements\AchievementRepository
 */
final class AchievementRepositoryTest extends CatrowebTestCase
{
  /**
   * @var AchievementRepository|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AchievementRepository::class)
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
    $this->assertTrue(class_exists(AchievementRepository::class));
    $this->assertInstanceOf(AchievementRepository::class, $this->object);
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
