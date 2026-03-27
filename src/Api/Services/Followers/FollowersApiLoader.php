<?php

declare(strict_types=1);

namespace App\Api\Services\Followers;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\UserRepository;
use Symfony\Component\Security\Core\Security;

class FollowersApiLoader
{
    public function __construct(
        private readonly UserRepository $user_repository,
        private readonly Security $security,
    ) {
    }

    /**
     * Returns the list of users who follow the given user.
     *
     * Joins on f.following so that we find rows where the given user
     * is the one being followed (i.e. f.following = user), then returns
     * f.follower — the accounts that performed the follow action.
     *
     * Previously this accidentally queried f.followers (the inverse side),
     * which returned who the user follows rather than who follows them.
     * See: https://github.com/Catrobat/Catroweb/issues/6299
     */
    public function getFollowers(string $username, int $limit, int $offset): array
    {
        /** @var User|null $user */
        $user = $this->user_repository->findUserByUsername($username);
        if (null === $user) {
            return [];
        }

        return $this->user_repository->createQueryBuilder('u')
            ->innerJoin('u.followers', 'f')         // f = UserFollowing row
            ->innerJoin('f.following', 'followed')  // followed = the user being followed
            ->where('followed = :user')
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the list of users that the given user follows.
     *
     * Joins on f.follower so that we find rows where the given user
     * is the one doing the following (i.e. f.follower = user), then
     * returns f.following — the accounts the user has subscribed to.
     *
     * Previously this accidentally queried f.following (the inverse side),
     * which returned who follows the user rather than who the user follows.
     * See: https://github.com/Catrobat/Catroweb/issues/6299
     */
    public function getFollowing(string $username, int $limit, int $offset): array
    {
        /** @var User|null $user */
        $user = $this->user_repository->findUserByUsername($username);
        if (null === $user) {
            return [];
        }

        return $this->user_repository->createQueryBuilder('u')
            ->innerJoin('u.following', 'f')          // f = UserFollowing row
            ->innerJoin('f.follower', 'follower')    // follower = the user who initiated the follow
            ->where('follower = :user')
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }
}