<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentModerationActionRepository;
use App\DB\EntityRepository\Moderation\ContentReportRepository;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

class TrustScoreCalculator
{
  private const float MAX_BASE_SCORE = 2.0;
  private const float MAX_ACTIVITY_SCORE = 3.0;
  private const float MIN_ACCURACY_SCORE = -5.0;
  private const float MAX_ACCURACY_SCORE = 5.0;
  private const float ROLE_BONUS = 100.0;
  private const float VERIFIED_BONUS = 0.5;
  private const float PROBATION_PENALTY_PER_HIDE = -1.0;
  private const float MAX_PROBATION_PENALTY = -3.0;
  private const int PROBATION_WINDOW_DAYS = 90;
  private const int CACHE_TTL = 900; // 15 minutes
  private const string CACHE_PREFIX = 'trust_score_';

  public function __construct(
    private readonly ContentReportRepository $report_repository,
    private readonly ProgramRepository $program_repository,
    private readonly UserCommentRepository $comment_repository,
    private readonly CacheItemPoolInterface $cache,
    private readonly ContentModerationActionRepository $action_repository,
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  public function calculate(User $user): float
  {
    $user_id = $user->getId();
    $cache_key = self::CACHE_PREFIX.$user_id;

    $item = $this->cache->getItem($cache_key);
    if ($item->isHit()) {
      return (float) $item->get();
    }

    $score = $this->computeScore($user);

    $item->set($score);
    $item->expiresAfter(self::CACHE_TTL);
    $this->cache->save($item);

    return $score;
  }

  public function invalidate(User $user): void
  {
    $this->cache->deleteItem(self::CACHE_PREFIX.$user->getId());
  }

  private function computeScore(User $user): float
  {
    $base = $this->computeBaseScore($user);
    $activity = $this->computeActivityScore($user);
    $accuracy = $this->computeAccuracyScore($user);
    $role_bonus = $this->computeRoleBonus($user);
    $verified_bonus = $this->computeVerifiedBonus($user);
    $probation_penalty = $this->computeProbationPenalty($user);

    return max(0.0, $base + $activity + $accuracy + $role_bonus + $verified_bonus + $probation_penalty);
  }

  private function computeBaseScore(User $user): float
  {
    $created_at = $user->getCreatedAt();
    if (null === $created_at) {
      return 0.0;
    }

    $age_days = (new \DateTime())->diff($created_at)->days;

    return min($age_days / 365.0, self::MAX_BASE_SCORE);
  }

  private function computeActivityScore(User $user): float
  {
    $user_id = $user->getId();
    $project_count = $this->program_repository->count(['user' => $user_id]);
    $comment_count = $this->comment_repository->count(['user' => $user_id]);
    $follower_count = (int) $this->entity_manager->createQueryBuilder()
      ->select('COUNT(f)')
      ->from(User::class, 'f')
      ->join('f.following', 'u')
      ->where('u.id = :user_id')
      ->setParameter('user_id', $user_id)
      ->getQuery()
      ->getSingleScalarResult()
    ;

    $score = ($project_count * 0.3) + ($comment_count * 0.05) + ($follower_count * 0.02);

    return min($score, self::MAX_ACTIVITY_SCORE);
  }

  private function computeAccuracyScore(User $user): float
  {
    $counts = $this->report_repository->getWeightedReportAccuracy($user->getId());

    $score = ($counts['accepted'] * 1.5) - ($counts['rejected'] * 2.0);

    return max(self::MIN_ACCURACY_SCORE, min($score, self::MAX_ACCURACY_SCORE));
  }

  private function computeRoleBonus(User $user): float
  {
    if ($user->hasRole('ROLE_MODERATOR') || $user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')) {
      return self::ROLE_BONUS;
    }

    return 0.0;
  }

  private function computeVerifiedBonus(User $user): float
  {
    return $user->isVerified() ? self::VERIFIED_BONUS : 0.0;
  }

  private function computeProbationPenalty(User $user): float
  {
    $count = $this->action_repository->countRecentAutoHidesForUser(
      $user->getId(),
      self::PROBATION_WINDOW_DAYS
    );

    if (0 === $count) {
      return 0.0;
    }

    return max(self::MAX_PROBATION_PENALTY, self::PROBATION_PENALTY_PER_HIDE * $count);
  }
}
