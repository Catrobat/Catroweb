<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\UserRepository;
use App\DB\EntityRepository\Project\ProgramRepository;

/**
 * Calculates a trust score for a given user.
 *
 * The score is a weighted sum of several signals. Higher scores indicate
 * higher trustworthiness and are used by moderation tooling to prioritise
 * manual reviews.
 *
 * Fix applied (issue #6299): the $follower_count calculation previously
 * joined on `f.follower` (i.e. counted outbound follows) instead of
 * `f.following` (i.e. counted inbound followers). This caused the trust
 * score to reflect how many accounts a user follows rather than how popular
 * the user is — a signal with opposite meaning. The DQL join is now corrected.
 */
class TrustScoreCalculator
{
    // Scoring weights — adjust to tune moderation sensitivity.
    private const WEIGHT_UPLOAD_COUNT    = 2;
    private const WEIGHT_FOLLOWER_COUNT  = 3;
    private const WEIGHT_DOWNLOAD_COUNT  = 1;
    private const WEIGHT_ACCOUNT_AGE     = 5;   // points per year

    public function __construct(
        private readonly UserRepository    $user_repository,
        private readonly ProgramRepository $program_repository,
    ) {
    }

    public function calculate(User $user): float
    {
        $upload_count   = $this->getUploadCount($user);
        $follower_count = $this->getFollowerCount($user);  // inbound followers
        $download_count = $this->getDownloadCount($user);
        $account_age    = $this->getAccountAgeInYears($user);

        return
            ($upload_count   * self::WEIGHT_UPLOAD_COUNT)
            + ($follower_count * self::WEIGHT_FOLLOWER_COUNT)
            + ($download_count * self::WEIGHT_DOWNLOAD_COUNT)
            + ($account_age   * self::WEIGHT_ACCOUNT_AGE);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function getUploadCount(User $user): int
    {
        return $this->program_repository->countPublicUserProjects($user->getId());
    }

    /**
     * Counts the number of users who follow $user (inbound follower count).
     *
     * Previously used `f.follower = :user` which counted how many accounts
     * $user themselves follow (outbound), producing an inverted signal.
     * Corrected to `f.following = :user` so we count incoming followers.
     *
     * @see https://github.com/Catrobat/Catroweb/issues/6299
     */
    private function getFollowerCount(User $user): int
    {
        return (int) $this->user_repository->createQueryBuilder('u')
            ->select('COUNT(f)')
            ->innerJoin('u.followers', 'f')        // UserFollowing rows where u is followed
            ->innerJoin('f.following', 'followed') // the account being followed
            ->where('followed = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getDownloadCount(User $user): int
    {
        return $user->getNumberOfProjects() > 0
            ? $this->program_repository->getTotalDownloadsOfUser($user->getId())
            : 0;
    }

    private function getAccountAgeInYears(User $user): float
    {
        $created_at = $user->getCreatedAt();
        if (null === $created_at) {
            return 0.0;
        }

        $diff = $created_at->diff(new \DateTimeImmutable());

        return $diff->y + ($diff->m / 12);
    }
}