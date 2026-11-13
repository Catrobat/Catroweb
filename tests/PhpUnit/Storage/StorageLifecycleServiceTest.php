<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Storage;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\Notifications\ProjectFilesLostNotification;
use App\DB\Entity\User\User;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Storage\ScreenshotRepository;
use App\Storage\StorageLifecycleService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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

    self::assertSame(StorageLifecycleService::PROTECTED_DAYS, $this->service->getRetentionTier($project));
    self::assertTrue($this->service->isProtected($project));
  }

  public function testProtectedTierForFeaturedProject(): void
  {
    $service = $this->buildServiceWithCounts(1, 0);

    $project = $this->createProjectStub();

    self::assertTrue($service->isProtected($project));
    self::assertSame(StorageLifecycleService::PROTECTED_DAYS, $service->getRetentionTier($project));
  }

  public function testProtectedTierForExampleProject(): void
  {
    $service = $this->buildServiceWithCounts(0, 1);

    $project = $this->createProjectStub();

    self::assertTrue($service->isProtected($project));
    self::assertSame(StorageLifecycleService::PROTECTED_DAYS, $service->getRetentionTier($project));
  }

  public function testActiveTierHighDownloads(): void
  {
    $project = $this->createProjectStub(downloads: 50, visible: true);

    self::assertTrue($this->service->isActive($project));
    self::assertSame(StorageLifecycleService::ACTIVE_DAYS, $this->service->getRetentionTier($project));
  }

  public function testActiveTierRecentUserLogin(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-30 days'));
    $user->method('isVerified')->willReturn(true);

    $project = $this->createProjectStub(downloads: 0, visible: true, user: $user);

    self::assertTrue($this->service->isActive($project));
    self::assertSame(StorageLifecycleService::ACTIVE_DAYS, $this->service->getRetentionTier($project));
  }

  public function testStandardTierVisibleVerifiedUser(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-200 days'));
    $user->method('isVerified')->willReturn(true);

    $project = $this->createProjectStub(downloads: 5, visible: true, autoHidden: false, user: $user);

    self::assertTrue($this->service->isStandard($project));
    self::assertSame(StorageLifecycleService::STANDARD_DAYS, $this->service->getRetentionTier($project));
  }

  public function testShortTierZeroDownloadsInactiveUser(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-365 days'));
    $user->method('isVerified')->willReturn(false);

    $project = $this->createProjectStub(downloads: 0, visible: true, autoHidden: false, user: $user);

    self::assertFalse($this->service->isActive($project));
    self::assertFalse($this->service->isStandard($project));
    self::assertSame(StorageLifecycleService::SHORT_DAYS, $this->service->getRetentionTier($project));
  }

  public function testDebugTierForDebugBuild(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-365 days'));
    $user->method('isVerified')->willReturn(false);

    $project = $this->createProjectStub(downloads: 0, visible: true, autoHidden: false, debugBuild: true, user: $user);

    self::assertSame(StorageLifecycleService::DEBUG_DAYS, $this->service->getRetentionTier($project));
  }

  public function testHiddenProjectReturnsZeroDays(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-200 days'));
    $user->method('isVerified')->willReturn(true);

    $project = $this->createProjectStub(downloads: 5, visible: false, user: $user);

    self::assertSame(StorageLifecycleService::HIDDEN_DAYS, $this->service->getRetentionTier($project));
  }

  public function testShortTierForAutoHiddenProject(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-200 days'));
    $user->method('isVerified')->willReturn(true);

    $project = $this->createProjectStub(downloads: 5, visible: true, autoHidden: true, user: $user);

    self::assertFalse($this->service->isStandard($project));
    self::assertSame(StorageLifecycleService::SHORT_DAYS, $this->service->getRetentionTier($project));
  }

  public function testShortTierForUnverifiedUser(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getLastLogin')->willReturn(new \DateTime('-200 days'));
    $user->method('isVerified')->willReturn(false);

    $project = $this->createProjectStub(downloads: 5, visible: true, autoHidden: false, user: $user);

    self::assertFalse($this->service->isStandard($project));
    self::assertSame(StorageLifecycleService::SHORT_DAYS, $this->service->getRetentionTier($project));
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

  public function testDeletesEveryExpiredProjectAcrossBatches(): void
  {
    $table = [];
    for ($i = 0; $i < 150; ++$i) {
      $table[] = $this->createProjectStub();
    }

    $result = $this->buildServiceOverTable($table, flushFails: false)->deleteExpiredProjects();

    self::assertSame(['checked' => 150, 'deleted' => 150, 'errors' => 0], $result);
    self::assertSame([], $table);
  }

  public function testStopsOnceTheEntityManagerIsClosed(): void
  {
    $table = [$this->createProjectStub(), $this->createProjectStub()];

    $result = $this->buildServiceOverTable($table, flushFails: true)->deleteExpiredProjects();

    self::assertSame(['checked' => 1, 'deleted' => 0, 'errors' => 1], $result);
  }

  public function testDeletesProjectWithLostFilesFifteenDaysAfterTheNotice(): void
  {
    $table = [$this->createLostFilesProjectStub('lost', hasMissingFiles: true)];

    $result = $this->buildServiceOverTable($table, flushFails: false, notices: [$this->createNotice('lost', '-15 days')])->deleteExpiredProjects();

    self::assertSame(1, $result['deleted']);
    self::assertSame([], $table);
  }

  public function testKeepsProjectWithLostFilesThirteenDaysAfterTheNotice(): void
  {
    $table = [$this->createLostFilesProjectStub('lost', hasMissingFiles: true)];

    $result = $this->buildServiceOverTable($table, flushFails: false, notices: [$this->createNotice('lost', '-13 days')])->deleteExpiredProjects();

    self::assertSame(0, $result['deleted']);
    self::assertCount(1, $table);
  }

  public function testKeepsRestoredProjectAfterTheNotice(): void
  {
    $table = [$this->createLostFilesProjectStub('restored', hasMissingFiles: false)];

    $result = $this->buildServiceOverTable($table, flushFails: false, notices: [$this->createNotice('restored', '-15 days')])->deleteExpiredProjects();

    self::assertSame(0, $result['deleted']);
    self::assertCount(1, $table);
  }

  public function testKeepsProjectLostAgainAfterARestoreUntilItIsNoticedAgain(): void
  {
    // Notified 20 days ago, re-uploaded 5 days ago, flagged again since: the old notice is about the earlier loss.
    $table = [$this->createLostFilesProjectStub('lost', hasMissingFiles: true, uploadedAt: '-5 days')];

    $result = $this->buildServiceOverTable($table, flushFails: false, notices: [$this->createNotice('lost', '-20 days')])->deleteExpiredProjects();

    self::assertSame(0, $result['deleted']);
    self::assertCount(1, $table);
  }

  public function testKeepsProjectWithLostFilesWhoseOwnerWasNeverNotified(): void
  {
    $table = [$this->createLostFilesProjectStub('lost', hasMissingFiles: true)];

    $result = $this->buildServiceOverTable($table, flushFails: false, notices: [$this->createNotice('other', '-15 days')])->deleteExpiredProjects();

    self::assertSame(0, $result['deleted']);
    self::assertCount(1, $table);
  }

  public function testDeletesLostScratchImportWithoutWaitingForANotice(): void
  {
    $table = [$this->createLostFilesProjectStub('lost', hasMissingFiles: true, scratchId: 123)];

    $result = $this->buildServiceOverTable($table, flushFails: false)->deleteExpiredProjects();

    self::assertSame(1, $result['deleted']);
    self::assertSame([], $table);
  }

  public function testKeepsIntactScratchImport(): void
  {
    $table = [$this->createLostFilesProjectStub('intact', hasMissingFiles: false, scratchId: 123)];

    $result = $this->buildServiceOverTable($table, flushFails: false)->deleteExpiredProjects();

    self::assertSame(0, $result['deleted']);
    self::assertCount(1, $table);
  }

  /**
   * An active project that normal retention keeps for a year.
   */
  private function createLostFilesProjectStub(string $id, bool $hasMissingFiles, string $uploadedAt = '-30 days', ?int $scratchId = null): Project
  {
    $project = $this->createStub(Project::class);
    $project->method('getId')->willReturn($id);
    $project->method('getScratchId')->willReturn($scratchId);
    $project->method('hasMissingFiles')->willReturn($hasMissingFiles);
    $project->method('getDownloads')->willReturn(100);
    $project->method('getVisible')->willReturn(true);
    $project->method('getUser')->willReturn($this->createStub(User::class));
    $project->method('getUploadedAt')->willReturn(new \DateTime($uploadedAt));
    $project->method('getName')->willReturn('Lost Project');

    return $project;
  }

  private function createNotice(string $projectId, string $age): ProjectFilesLostNotification
  {
    $notice = new ProjectFilesLostNotification($this->createStub(User::class), [$projectId => 'Lost Project']);
    $notice->setCreatedAt(new \DateTime($age));

    return $notice;
  }

  /**
   * @param list<Project>                      $table   rows; removed projects are dropped from it on flush
   * @param list<ProjectFilesLostNotification> $notices
   */
  private function buildServiceOverTable(array &$table, bool $flushFails, array $notices = []): StorageLifecycleService
  {
    $offset = 0;
    $removed = null;
    $open = true;

    $query = $this->createStub(Query::class);
    $query->method('getSingleColumnResult')->willReturn([]);
    $query->method('getResult')->willReturnCallback(static function () use (&$table, &$offset): array {
      return array_slice($table, $offset, 100);
    });

    $qb = $this->createStub(QueryBuilder::class);
    foreach (['select', 'from', 'where', 'setParameter', 'leftJoin', 'orderBy', 'setMaxResults'] as $method) {
      $qb->method($method)->willReturnSelf();
    }
    $qb->method('setFirstResult')->willReturnCallback(static function (int $first) use (&$offset, $qb): QueryBuilder {
      $offset = $first;

      return $qb;
    });
    $qb->method('getQuery')->willReturn($query);

    $notice_repository = $this->createStub(EntityRepository::class);
    $notice_repository->method('findAll')->willReturn($notices);

    $em = $this->createStub(EntityManagerInterface::class);
    $em->method('createQueryBuilder')->willReturn($qb);
    $em->method('getRepository')->willReturn($notice_repository);
    $em->method('isOpen')->willReturnCallback(static function () use (&$open): bool {
      return $open;
    });
    $em->method('remove')->willReturnCallback(static function (object $project) use (&$removed): void {
      $removed = $project;
    });
    $em->method('flush')->willReturnCallback(static function () use (&$table, &$removed, &$open, $flushFails): void {
      if (null === $removed) {
        return;
      }
      if ($flushFails) {
        $open = false;

        throw new \RuntimeException('foreign key constraint fails');
      }
      $table = array_values(array_filter($table, static fn (Project $p): bool => $p !== $removed));
      $removed = null;
    });

    return new StorageLifecycleService(
      $em,
      $this->createStub(ProjectFileRepository::class),
      $this->createStub(ScreenshotRepository::class),
      new NullLogger(),
    );
  }

  private function createProjectStub(
    bool $storageProtected = false,
    int $downloads = 0,
    bool $visible = true,
    bool $autoHidden = false,
    bool $debugBuild = false,
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
    $project->method('isDebugBuild')->willReturn($debugBuild);
    $project->method('getUser')->willReturn($user);
    $project->method('getUploadedAt')->willReturn(new \DateTime('-400 days'));
    $project->method('getName')->willReturn('Test Project');

    return $project;
  }

  private function buildServiceWithCounts(int $featured, int $example): StorageLifecycleService
  {
    $query = $this->createStub(Query::class);

    $callCount = 0;
    $query->method('getSingleColumnResult')
      ->willReturnCallback(function () use (&$callCount, $featured, $example): array {
        $count = 0 === $callCount++ % 2 ? $featured : $example;

        return $count > 0 ? ['test-project-id'] : [];
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
