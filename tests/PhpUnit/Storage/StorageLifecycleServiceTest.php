<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Storage;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Storage\ScreenshotRepository;
use App\Storage\StorageLifecycleService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @internal
 *
 * @covers \App\Storage\StorageLifecycleService
 */
class StorageLifecycleServiceTest extends TestCase
{
  private StorageLifecycleService $service;

  #[\Override]
  protected function setUp(): void
  {
    $this->service = $this->buildServiceWithCounts(0, 0);
  }

  public function testProtectedTierForStorageProtectedProject(): void
  {
    $project = $this->createProjectStub(storageProtected: true);

    self::assertSame(StorageLifecycleService::PROTECTED_DAYS, $this->service->getRetentionDays($project));
    self::assertTrue($this->service->isProtected($project));
  }

  public function testProtectedTierForFeaturedProject(): void
  {
    $service = $this->buildServiceWithCounts(1, 0);

    $project = $this->createProjectStub();

    self::assertTrue($service->isProtected($project));
    self::assertSame(StorageLifecycleService::PROTECTED_DAYS, $service->getRetentionDays($project));
  }

  public function testProtectedTierForExampleProject(): void
  {
    $service = $this->buildServiceWithCounts(0, 1);

    $project = $this->createProjectStub();

    self::assertTrue($service->isProtected($project));
    self::assertSame(StorageLifecycleService::PROTECTED_DAYS, $service->getRetentionDays($project));
  }

  public function testActiveTierHighDownloads(): void
  {
    $project = $this->createProjectStub(downloads: 50, visible: true);

    self::assertTrue($this->service->isActive($project));
    self::assertSame(StorageLifecycleService::ACTIVE_DAYS, $this->service->getRetentionDays($project));
  }

  public function testActiveTierRecentUserLogin(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-30 days'));
    $user->method('isVerified')->willReturn(true);

    $project = $this->createProjectStub(downloads: 0, visible: true, user: $user);

    self::assertTrue($this->service->isActive($project));
    self::assertSame(StorageLifecycleService::ACTIVE_DAYS, $this->service->getRetentionDays($project));
  }

  public function testStandardTierVisibleVerifiedUser(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-200 days'));
    $user->method('isVerified')->willReturn(true);

    $project = $this->createProjectStub(downloads: 5, visible: true, autoHidden: false, user: $user);

    self::assertTrue($this->service->isStandard($project));
    self::assertSame(StorageLifecycleService::STANDARD_DAYS, $this->service->getRetentionDays($project));
  }

  public function testShortTierZeroDownloadsInactiveUser(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-365 days'));
    $user->method('isVerified')->willReturn(false);

    $project = $this->createProjectStub(downloads: 0, visible: true, autoHidden: false, user: $user);

    self::assertFalse($this->service->isActive($project));
    self::assertFalse($this->service->isStandard($project));
    self::assertSame(StorageLifecycleService::SHORT_DAYS, $this->service->getRetentionDays($project));
  }

  public function testShortTierForHiddenProject(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-200 days'));
    $user->method('isVerified')->willReturn(true);

    $project = $this->createProjectStub(downloads: 5, visible: false, user: $user);

    self::assertFalse($this->service->isStandard($project));
    self::assertSame(StorageLifecycleService::SHORT_DAYS, $this->service->getRetentionDays($project));
  }

  public function testShortTierForAutoHiddenProject(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-200 days'));
    $user->method('isVerified')->willReturn(true);

    $project = $this->createProjectStub(downloads: 5, visible: true, autoHidden: true, user: $user);

    self::assertFalse($this->service->isStandard($project));
    self::assertSame(StorageLifecycleService::SHORT_DAYS, $this->service->getRetentionDays($project));
  }

  public function testShortTierForUnverifiedUser(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-200 days'));
    $user->method('isVerified')->willReturn(false);

    $project = $this->createProjectStub(downloads: 5, visible: true, autoHidden: false, user: $user);

    self::assertFalse($this->service->isStandard($project));
    self::assertSame(StorageLifecycleService::SHORT_DAYS, $this->service->getRetentionDays($project));
  }

  public function testDiskPressureHalvesRetention(): void
  {
    $result = $this->service->applyDiskPressure(90, 0.87);
    self::assertSame(45, $result);
  }

  public function testDiskCriticalQuartersRetention(): void
  {
    $result = $this->service->applyDiskPressure(90, 0.96);
    self::assertSame(23, $result);
  }

  public function testDiskPressureDoesNotAffectProtected(): void
  {
    $result = $this->service->applyDiskPressure(StorageLifecycleService::PROTECTED_DAYS, 0.99);
    self::assertSame(StorageLifecycleService::PROTECTED_DAYS, $result);
  }

  public function testNormalDiskDoesNotChangeRetention(): void
  {
    $result = $this->service->applyDiskPressure(90, 0.50);
    self::assertSame(90, $result);
  }

  public function testShouldPauseUploadsAtCritical(): void
  {
    self::assertTrue($this->service->shouldPauseUploads(0.96));
    self::assertFalse($this->service->shouldPauseUploads(0.94));
    self::assertFalse($this->service->shouldPauseUploads(0.50));
  }

  private function createProjectStub(
    bool $storageProtected = false,
    int $downloads = 0,
    bool $visible = true,
    bool $autoHidden = false,
    ?User $user = null,
  ): Project {
    if (null === $user) {
      $user = $this->createStub(User::class);
      $user->method('getLastLogin')->willReturn(null);
      $user->method('isVerified')->willReturn(false);
    }

    $project = $this->createStub(Project::class);
    $project->method('getId')->willReturn('test-project-id');
    $project->method('isStorageProtected')->willReturn($storageProtected);
    $project->method('getDownloads')->willReturn($downloads);
    $project->method('getVisible')->willReturn($visible);
    $project->method('getAutoHidden')->willReturn($autoHidden);
    $project->method('getUser')->willReturn($user);
    $project->method('getUploadedAt')->willReturn(new \DateTime('-400 days'));
    $project->method('getName')->willReturn('Test Project');

    return $project;
  }

  private function buildServiceWithCounts(int $featured, int $example): StorageLifecycleService
  {
    $query = $this->createStub(Query::class);

    $callCount = 0;
    $query->method('getSingleScalarResult')
      ->willReturnCallback(function () use (&$callCount, $featured, $example): int {
        return 0 === $callCount++ % 2 ? $featured : $example;
      })
    ;

    $qb = $this->createStub(QueryBuilder::class);
    $qb->method('select')->willReturnSelf();
    $qb->method('from')->willReturnSelf();
    $qb->method('where')->willReturnSelf();
    $qb->method('setParameter')->willReturnSelf();
    $qb->method('getQuery')->willReturn($query);

    $em = $this->createStub(EntityManagerInterface::class);
    $em->method('createQueryBuilder')->willReturn($qb);

    return new StorageLifecycleService(
      $em,
      $this->createStub(ProjectFileRepository::class),
      $this->createStub(ScreenshotRepository::class),
      new NullLogger(),
    );
  }
}
