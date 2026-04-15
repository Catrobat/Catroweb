<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentModerationActionRepository;
use App\DB\EntityRepository\Moderation\ContentReportRepository;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use App\Moderation\TrustScoreCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @internal
 */
#[CoversClass(TrustScoreCalculator::class)]
final class TrustScoreCalculatorTest extends TestCase
{
  private function buildCalculator(
    ?ContentReportRepository $report_repository = null,
    ?ProjectRepository $program_repository = null,
    ?UserCommentRepository $comment_repository = null,
    ?CacheItemPoolInterface $cache = null,
    ?ContentModerationActionRepository $action_repository = null,
    ?EntityManagerInterface $entity_manager = null,
  ): TrustScoreCalculator {
    if (!$cache instanceof CacheItemPoolInterface) {
      $cache_item = $this->createStub(CacheItemInterface::class);
      $cache_item->method('isHit')->willReturn(false);

      $cache = $this->createStub(CacheItemPoolInterface::class);
      $cache->method('getItem')->willReturn($cache_item);
    }

    if (!$report_repository instanceof ContentReportRepository) {
      $report_repository = $this->createStub(ContentReportRepository::class);
      $report_repository->method('getWeightedReportAccuracy')->willReturn(['accepted' => 0.0, 'rejected' => 0.0]);
    }

    return new TrustScoreCalculator(
      $report_repository,
      $program_repository ?? $this->createStub(ProjectRepository::class),
      $comment_repository ?? $this->createStub(UserCommentRepository::class),
      $cache,
      $action_repository ?? $this->createStub(ContentModerationActionRepository::class),
      $entity_manager ?? $this->createFollowerCountEntityManager(0),
    );
  }

  private function createFollowerCountEntityManager(int $count): EntityManagerInterface
  {
    $query = $this->createStub(Query::class);
    $query->method('getSingleScalarResult')->willReturn($count);

    $qb = $this->createStub(QueryBuilder::class);
    $qb->method('select')->willReturnSelf();
    $qb->method('from')->willReturnSelf();
    $qb->method('join')->willReturnSelf();
    $qb->method('where')->willReturnSelf();
    $qb->method('setParameter')->willReturnSelf();
    $qb->method('getQuery')->willReturn($query);

    $em = $this->createStub(EntityManagerInterface::class);
    $em->method('createQueryBuilder')->willReturn($qb);

    return $em;
  }

  private function createUserStub(
    ?\DateTimeInterface $created_at = null,
    array $roles = [],
    bool $verified = false,
  ): User {
    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn('test-user-id');
    $user->method('getCreatedAt')->willReturn($created_at);
    $user->method('hasRole')->willReturnCallback(fn (string $role): bool => in_array($role, $roles, true));
    $user->method('isVerified')->willReturn($verified);

    return $user;
  }

  #[Group('unit')]
  public function testNewUserHasZeroScore(): void
  {
    $user = $this->createUserStub(created_at: new \DateTime());
    $calculator = $this->buildCalculator();

    $score = $calculator->calculate($user);

    $this->assertEqualsWithDelta(0.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testBaseScoreIncreasesWithAccountAge(): void
  {
    $user = $this->createUserStub(created_at: new \DateTime('-365 days'));
    $calculator = $this->buildCalculator();

    $score = $calculator->calculate($user);

    $this->assertEqualsWithDelta(1.0, $score, 0.05);
  }

  #[Group('unit')]
  public function testBaseScoreCapsAtTwo(): void
  {
    $user = $this->createUserStub(created_at: new \DateTime('-1000 days'));
    $calculator = $this->buildCalculator();

    $score = $calculator->calculate($user);

    $this->assertEqualsWithDelta(2.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testActivityScoreCountsProjects(): void
  {
    $program_repo = $this->createStub(ProjectRepository::class);
    $program_repo->method('count')->willReturn(10);

    $user = $this->createUserStub(created_at: new \DateTime());
    $calculator = $this->buildCalculator(program_repository: $program_repo);

    $score = $calculator->calculate($user);

    // 10 projects * 0.3 = 3.0 (capped at max 3.0)
    $this->assertEqualsWithDelta(3.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testActivityScoreCapsAtThree(): void
  {
    $program_repo = $this->createStub(ProjectRepository::class);
    $program_repo->method('count')->willReturn(50);

    $user = $this->createUserStub(created_at: new \DateTime());
    $calculator = $this->buildCalculator(program_repository: $program_repo);

    $score = $calculator->calculate($user);

    $this->assertEqualsWithDelta(3.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testAccuracyScorePositiveForAcceptedReports(): void
  {
    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('getWeightedReportAccuracy')->willReturn(['accepted' => 4.0, 'rejected' => 0.0]);

    $user = $this->createUserStub(created_at: new \DateTime());
    $calculator = $this->buildCalculator(report_repository: $report_repo);

    $score = $calculator->calculate($user);

    // 4 * 1.5 = 6.0 capped at 5.0
    $this->assertEqualsWithDelta(5.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testAccuracyScoreNegativeForRejectedReports(): void
  {
    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('getWeightedReportAccuracy')->willReturn(['accepted' => 0.0, 'rejected' => 3.0]);

    // Need enough base score to avoid clamping to 0
    $user = $this->createUserStub(created_at: new \DateTime('-1000 days'));
    $calculator = $this->buildCalculator(report_repository: $report_repo);

    $score = $calculator->calculate($user);

    // base = 2.0, accuracy = 0 - 6.0 = -6.0 clamped to -5.0
    // total = max(0, 2.0 + 0 + (-5.0) + 0 + 0 + 0) = 0.0 (clamped to 0)
    $this->assertEqualsWithDelta(0.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testModeratorRoleBonusIsHundred(): void
  {
    $user = $this->createUserStub(created_at: new \DateTime(), roles: ['ROLE_MODERATOR']);
    $calculator = $this->buildCalculator();

    $score = $calculator->calculate($user);

    $this->assertEqualsWithDelta(100.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testAdminRoleBonusIsHundred(): void
  {
    $user = $this->createUserStub(created_at: new \DateTime(), roles: ['ROLE_ADMIN']);
    $calculator = $this->buildCalculator();

    $score = $calculator->calculate($user);

    $this->assertEqualsWithDelta(100.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testCacheIsUsedOnSecondCall(): void
  {
    $cache_item = $this->createStub(CacheItemInterface::class);
    $cache_item->method('isHit')->willReturn(true);
    $cache_item->method('get')->willReturn(42.5);

    $cache = $this->createStub(CacheItemPoolInterface::class);
    $cache->method('getItem')->willReturn($cache_item);

    $user = $this->createUserStub(created_at: new \DateTime());
    $calculator = $this->buildCalculator(cache: $cache);

    $score = $calculator->calculate($user);

    $this->assertEqualsWithDelta(42.5, $score, 0.01);
  }

  #[Group('unit')]
  public function testInvalidateClearsCache(): void
  {
    $cache = $this->createMock(CacheItemPoolInterface::class);
    $cache->expects($this->once())
      ->method('deleteItem')
      ->with('trust_score_test-user-id')
    ;

    $cache_item = $this->createStub(CacheItemInterface::class);
    $cache_item->method('isHit')->willReturn(false);
    $cache->method('getItem')->willReturn($cache_item);

    $user = $this->createUserStub(created_at: new \DateTime());
    $calculator = $this->buildCalculator(cache: $cache);

    $calculator->invalidate($user);
  }

  #[Group('unit')]
  public function testNullCreatedAtGivesZeroBaseScore(): void
  {
    $user = $this->createUserStub();
    $calculator = $this->buildCalculator();

    $score = $calculator->calculate($user);

    $this->assertEqualsWithDelta(0.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testVerifiedBonusAddsHalfPoint(): void
  {
    $user = $this->createUserStub(created_at: new \DateTime(), verified: true);
    $calculator = $this->buildCalculator();

    $score = $calculator->calculate($user);

    $this->assertEqualsWithDelta(0.5, $score, 0.01);
  }

  #[Group('unit')]
  public function testUnverifiedUserGetsNoBonus(): void
  {
    $user = $this->createUserStub(created_at: new \DateTime(), verified: false);
    $calculator = $this->buildCalculator();

    $score = $calculator->calculate($user);

    $this->assertEqualsWithDelta(0.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testProbationPenaltyReducesScore(): void
  {
    $action_repo = $this->createStub(ContentModerationActionRepository::class);
    $action_repo->method('countRecentAutoHidesForUser')->willReturn(2);

    // Need enough base score so penalty is visible
    $user = $this->createUserStub(created_at: new \DateTime('-1000 days'));
    $calculator = $this->buildCalculator(action_repository: $action_repo);

    $score = $calculator->calculate($user);

    // base = 2.0, probation = -2.0, total = 0.0
    $this->assertEqualsWithDelta(0.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testProbationPenaltyCapsAtMinusThree(): void
  {
    $action_repo = $this->createStub(ContentModerationActionRepository::class);
    $action_repo->method('countRecentAutoHidesForUser')->willReturn(10);

    $user = $this->createUserStub(created_at: new \DateTime('-1000 days'));
    $calculator = $this->buildCalculator(action_repository: $action_repo);

    $score = $calculator->calculate($user);

    // base = 2.0, probation = max(-3.0, -10.0) = -3.0, total = max(0, -1.0) = 0.0
    $this->assertEqualsWithDelta(0.0, $score, 0.01);
  }

  #[Group('unit')]
  public function testTemporalDecayUsesWeightedAccuracy(): void
  {
    $report_repo = $this->createStub(ContentReportRepository::class);
    // 2 recent accepted (weight 1.0 each) + 1 old accepted (weight 0.25)
    $report_repo->method('getWeightedReportAccuracy')->willReturn(['accepted' => 2.25, 'rejected' => 0.0]);

    $user = $this->createUserStub(created_at: new \DateTime());
    $calculator = $this->buildCalculator(report_repository: $report_repo);

    $score = $calculator->calculate($user);

    // accuracy = 2.25 * 1.5 = 3.375
    $this->assertEqualsWithDelta(3.375, $score, 0.01);
  }

  #[Group('unit')]
  public function testNoProbationPenaltyWhenNoAutoHides(): void
  {
    $action_repo = $this->createStub(ContentModerationActionRepository::class);
    $action_repo->method('countRecentAutoHidesForUser')->willReturn(0);

    $user = $this->createUserStub(created_at: new \DateTime('-365 days'), verified: true);
    $calculator = $this->buildCalculator(action_repository: $action_repo);

    $score = $calculator->calculate($user);

    // base ~1.0, verified = 0.5, probation = 0.0
    $this->assertEqualsWithDelta(1.5, $score, 0.05);
  }
}
