<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Admin\System\FeatureFlag;

use App\Admin\System\FeatureFlag\FeatureFlagManager;
use App\DB\Entity\System\FeatureFlag;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[CoversClass(FeatureFlagManager::class)]
final class FeatureFlagManagerTest extends TestCase
{
  public function testSynchronizeDefaultsPersistsMissingFlagsAndRemovesStaleFlags(): void
  {
    $repository = $this->createMock(EntityRepository::class);
    $entityManager = $this->createMock(EntityManagerInterface::class);

    $existingFlag = new FeatureFlag('Sidebar-Studio-Link-Feature', false);
    $staleFlag = new FeatureFlag('Deprecated-Flag', true);

    $entityManager
      ->expects($this->once())
      ->method('getRepository')
      ->with(FeatureFlag::class)
      ->willReturn($repository)
    ;

    $repository
      ->expects($this->once())
      ->method('findAll')
      ->willReturn([$existingFlag, $staleFlag])
    ;

    $entityManager
      ->expects($this->once())
      ->method('persist')
      ->with($this->callback(static fn ($flag): bool => $flag instanceof FeatureFlag && 'Test-Flag' === $flag->getName()))
    ;

    $entityManager
      ->expects($this->once())
      ->method('remove')
      ->with($staleFlag)
    ;

    $entityManager
      ->expects($this->once())
      ->method('flush')
    ;

    $manager = new FeatureFlagManager(
      $this->createStub(RequestStack::class),
      $entityManager,
      [
        'Test-Flag' => false,
        'Sidebar-Studio-Link-Feature' => false,
      ],
    );

    $manager->synchronizeDefaults();
  }
}
