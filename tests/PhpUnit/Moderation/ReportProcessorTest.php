<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentReportRepository;
use App\DB\Enum\ContentType;
use App\Moderation\AutoModerationService;
use App\Moderation\ContentVisibilityManager;
use App\Moderation\ReportException;
use App\Moderation\ReportProcessor;
use App\Moderation\TrustScoreCalculator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

/**
 * @internal
 */
#[CoversClass(ReportProcessor::class)]
final class ReportProcessorTest extends TestCase
{
  private function buildProcessor(
    ?ContentReportRepository $report_repository = null,
    ?TrustScoreCalculator $trust_calculator = null,
    ?AutoModerationService $auto_moderation = null,
    ?ContentVisibilityManager $visibility_manager = null,
    ?EntityManagerInterface $entity_manager = null,
    ?RateLimiterFactoryInterface $burst_limiter = null,
    ?RateLimiterFactoryInterface $daily_limiter = null,
  ): ReportProcessor {
    return new ReportProcessor(
      $report_repository ?? $this->createStub(ContentReportRepository::class),
      $trust_calculator ?? $this->createStub(TrustScoreCalculator::class),
      $auto_moderation ?? $this->createStub(AutoModerationService::class),
      $visibility_manager ?? $this->createStub(ContentVisibilityManager::class),
      $entity_manager ?? $this->createStub(EntityManagerInterface::class),
      $burst_limiter ?? $this->createAcceptingLimiterFactory(),
      $daily_limiter ?? $this->createAcceptingLimiterFactory(),
    );
  }

  private function createUserStub(string $id = 'reporter-id', bool $verified = true, array $roles = []): User
  {
    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn($id);
    $user->method('isVerified')->willReturn($verified);
    $user->method('hasRole')->willReturnCallback(fn (string $role): bool => in_array($role, $roles, true));

    return $user;
  }

  private function createAcceptingLimiterFactory(): RateLimiterFactoryInterface
  {
    $rate_limit = new RateLimit(10, new \DateTimeImmutable('+1 hour'), true, 10);

    $limiter = $this->createStub(LimiterInterface::class);
    $limiter->method('consume')->willReturn($rate_limit);

    $factory = $this->createStub(RateLimiterFactoryInterface::class);
    $factory->method('create')->willReturn($limiter);

    return $factory;
  }

  private function createRejectingLimiterFactory(): RateLimiterFactoryInterface
  {
    $rate_limit = new RateLimit(0, new \DateTimeImmutable('+15 minutes'), false, 3);

    $limiter = $this->createStub(LimiterInterface::class);
    $limiter->method('consume')->willReturn($rate_limit);

    $factory = $this->createStub(RateLimiterFactoryInterface::class);
    $factory->method('create')->willReturn($limiter);

    return $factory;
  }

  #[Group('unit')]
  public function testContentNotFoundThrowsException(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(false);

    $processor = $this->buildProcessor(visibility_manager: $visibility);

    $this->expectException(ReportException::class);

    $processor->processReport(
      $this->createUserStub(),
      ContentType::Project,
      'nonexistent-id',
      'spam',
    );
  }

  #[Group('unit')]
  public function testInvalidCategoryThrowsException(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(5.0);

    $processor = $this->buildProcessor(
      trust_calculator: $trust,
      visibility_manager: $visibility,
    );

    $this->expectException(ReportException::class);

    $processor->processReport(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'nonexistent_category',
    );
  }

  #[Group('unit')]
  public function testTrustTooLowThrowsException(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(0.1);

    $processor = $this->buildProcessor(
      trust_calculator: $trust,
      visibility_manager: $visibility,
    );

    $this->expectException(ReportException::class);

    $processor->processReport(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'spam',
    );
  }

  #[Group('unit')]
  public function testCannotReportOwnContentThrowsException(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('getContentOwnerId')->willReturn('reporter-id');

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(5.0);

    $processor = $this->buildProcessor(
      trust_calculator: $trust,
      visibility_manager: $visibility,
    );

    $this->expectException(ReportException::class);

    $processor->processReport(
      $this->createUserStub('reporter-id'),
      ContentType::Project,
      'project-id',
      'spam',
    );
  }

  #[Group('unit')]
  public function testDuplicateReportThrowsException(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('getContentOwnerId')->willReturn('other-user');

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(5.0);

    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('hasUserAlreadyReported')->willReturn(true);

    $processor = $this->buildProcessor(
      report_repository: $report_repo,
      trust_calculator: $trust,
      visibility_manager: $visibility,
    );

    $this->expectException(ReportException::class);

    $processor->processReport(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'spam',
    );
  }

  #[Group('unit')]
  public function testSuccessfulReportCreation(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('getContentOwnerId')->willReturn('other-user');

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(2.0);

    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('hasUserAlreadyReported')->willReturn(false);
    $report_repo->method('getCumulativeTrustScore')->willReturn(2.0);

    $em = $this->createStub(EntityManagerInterface::class);

    $processor = $this->buildProcessor(
      report_repository: $report_repo,
      trust_calculator: $trust,
      visibility_manager: $visibility,
      entity_manager: $em,
    );

    $result = $processor->processReport(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'spam',
      'This is spam',
    );

    $this->assertFalse($result['auto_hidden']);
  }

  #[Group('unit')]
  public function testAutoHideTriggeredWhenThresholdReached(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('getContentOwnerId')->willReturn('other-user');
    $visibility->method('isContentHidden')->willReturn(false);

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(5.0);

    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('hasUserAlreadyReported')->willReturn(false);
    $report_repo->method('getCumulativeTrustScore')->willReturn(12.0);
    $report_repo->method('getRecentReportVelocity')->willReturn(2);

    $auto_mod = $this->createMock(AutoModerationService::class);
    $auto_mod->expects($this->once())
      ->method('autoHideContent')
      ->with(ContentType::Project, 'project-id', 12.0)
    ;

    $em = $this->createStub(EntityManagerInterface::class);

    $processor = $this->buildProcessor(
      report_repository: $report_repo,
      trust_calculator: $trust,
      auto_moderation: $auto_mod,
      visibility_manager: $visibility,
      entity_manager: $em,
    );

    $result = $processor->processReport(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'spam',
    );

    $this->assertTrue($result['auto_hidden']);
  }

  #[Group('unit')]
  public function testAlreadyHiddenContentCannotBeReported(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('isContentHidden')->willReturn(true);

    $processor = $this->buildProcessor(visibility_manager: $visibility);

    $this->expectException(ReportException::class);
    $this->expectExceptionCode(409);

    $processor->processReport(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'spam',
    );
  }

  #[Group('unit')]
  public function testRateLimitBurstRejectsReport(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('getContentOwnerId')->willReturn('other-user');

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(5.0);

    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('hasUserAlreadyReported')->willReturn(false);

    $processor = $this->buildProcessor(
      report_repository: $report_repo,
      trust_calculator: $trust,
      visibility_manager: $visibility,
      burst_limiter: $this->createRejectingLimiterFactory(),
    );

    $this->expectException(ReportException::class);
    $this->expectExceptionCode(429);

    $processor->processReport(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'spam',
    );
  }

  #[Group('unit')]
  public function testRateLimitDailyRejectsReport(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('getContentOwnerId')->willReturn('other-user');

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(5.0);

    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('hasUserAlreadyReported')->willReturn(false);

    $processor = $this->buildProcessor(
      report_repository: $report_repo,
      trust_calculator: $trust,
      visibility_manager: $visibility,
      daily_limiter: $this->createRejectingLimiterFactory(),
    );

    $this->expectException(ReportException::class);
    $this->expectExceptionCode(429);

    $processor->processReport(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'spam',
    );
  }

  #[Group('unit')]
  public function testAdminBypassesRateLimit(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('getContentOwnerId')->willReturn('other-user');

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(100.0);

    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('hasUserAlreadyReported')->willReturn(false);
    $report_repo->method('getCumulativeTrustScore')->willReturn(2.0);

    $em = $this->createStub(EntityManagerInterface::class);

    $processor = $this->buildProcessor(
      report_repository: $report_repo,
      trust_calculator: $trust,
      visibility_manager: $visibility,
      entity_manager: $em,
      burst_limiter: $this->createRejectingLimiterFactory(),
      daily_limiter: $this->createRejectingLimiterFactory(),
    );

    $result = $processor->processReport(
      $this->createUserStub('admin-id', true, ['ROLE_ADMIN']),
      ContentType::Project,
      'project-id',
      'spam',
    );

    $this->assertFalse($result['auto_hidden']);
  }

  #[Group('unit')]
  public function testReportWeightCappedForNonAdmin(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('getContentOwnerId')->willReturn('other-user');

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(8.0);

    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('hasUserAlreadyReported')->willReturn(false);
    $report_repo->method('getCumulativeTrustScore')->willReturn(5.0);

    $em = $this->createStub(EntityManagerInterface::class);

    $processor = $this->buildProcessor(
      report_repository: $report_repo,
      trust_calculator: $trust,
      visibility_manager: $visibility,
      entity_manager: $em,
    );

    $result = $processor->processReport(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'spam',
    );

    // Trust is 8.0 but capped at 5.0 for non-admin
    $report = $result['report'];
    $this->assertEqualsWithDelta(5.0, $report->getReporterTrustScore(), 0.01);
  }

  #[Group('unit')]
  public function testAdminReportWeightNotCapped(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('getContentOwnerId')->willReturn('other-user');

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(100.0);

    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('hasUserAlreadyReported')->willReturn(false);
    $report_repo->method('getCumulativeTrustScore')->willReturn(100.0);

    $em = $this->createStub(EntityManagerInterface::class);

    $processor = $this->buildProcessor(
      report_repository: $report_repo,
      trust_calculator: $trust,
      visibility_manager: $visibility,
      entity_manager: $em,
      burst_limiter: $this->createAcceptingLimiterFactory(),
      daily_limiter: $this->createAcceptingLimiterFactory(),
    );

    $result = $processor->processReport(
      $this->createUserStub('admin-id', true, ['ROLE_ADMIN']),
      ContentType::Project,
      'project-id',
      'spam',
    );

    $this->assertEqualsWithDelta(100.0, $result['report']->getReporterTrustScore(), 0.01);
  }

  #[Group('unit')]
  public function testUnverifiedUserCannotReport(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);

    $processor = $this->buildProcessor(visibility_manager: $visibility);

    $this->expectException(ReportException::class);
    $this->expectExceptionCode(403);

    $processor->processReport(
      $this->createUserStub('user-id', false),
      ContentType::Project,
      'project-id',
      'spam',
    );
  }

  #[Group('unit')]
  public function testBrigadingDetectionSkipsAutoHide(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('getContentOwnerId')->willReturn('other-user');
    $visibility->method('isContentHidden')->willReturn(false);

    $trust = $this->createStub(TrustScoreCalculator::class);
    $trust->method('calculate')->willReturn(3.0);

    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('hasUserAlreadyReported')->willReturn(false);
    $report_repo->method('getCumulativeTrustScore')->willReturn(15.0);
    $report_repo->method('getRecentReportVelocity')->willReturn(6);

    $auto_mod = $this->createMock(AutoModerationService::class);
    $auto_mod->expects($this->never())->method('autoHideContent');
    $auto_mod->expects($this->once())
      ->method('notifyAdminsOfSuspectedBrigading')
      ->with(ContentType::Project, 'project-id', 15.0, 6)
    ;

    $em = $this->createStub(EntityManagerInterface::class);

    $processor = $this->buildProcessor(
      report_repository: $report_repo,
      trust_calculator: $trust,
      auto_moderation: $auto_mod,
      visibility_manager: $visibility,
      entity_manager: $em,
    );

    $result = $processor->processReport(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'spam',
    );

    $this->assertFalse($result['auto_hidden']);
  }
}
