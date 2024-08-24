<?php

declare(strict_types=1);

namespace App\Application\Controller\User;

use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\User\Notification\NotificationManager;
use App\User\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FollowerController extends AbstractController
{
  public function __construct(private readonly UserManager $user_manager, private readonly NotificationManager $notification_service, private readonly NotificationRepository $notification_repo)
  {
  }

  #[Route(path: '/follower', name: 'catrobat_follower', methods: ['GET'])]
  public function follower(Request $request, string $id = '0'): Response
  {
    $page = $request->request->getInt('page');
    $pageSize = $request->request->getInt('pageSize');
    /** @var User|null $user */
    $user = $this->getUser();
    if ('0' !== $id && !($user && $user->getId() === $id)) {
      /** @var User|null $user */
      $user = $this->user_manager->find($id);
    }

    if (null === $user) {
      return $this->redirectToRoute('login');
    }

    $criteria = Criteria::create()
      ->orderBy(['username' => Criteria::ASC])
      ->setFirstResult($page * $pageSize)
      ->setMaxResults($pageSize)
    ;
    /** @var ArrayCollection $followersCollection */
    $followersCollection = $user->getFollowers();
    /** @var ArrayCollection $followingCollection */
    $followingCollection = $user->getFollowing();
    $followersCollection->first();
    $followingCollection->first();
    $followers = $followersCollection->matching($criteria)->toArray();
    $following = $followingCollection->matching($criteria)->toArray();
    \Locale::setDefault(substr($request->getLocale(), 0, 2));
    $data_followers = $this->user_manager->getMappedUserData($followers);
    $data_following = $this->user_manager->getMappedUserData($following);

    return $this->render('User/Followers/FollowersPage.html.twig', [
      'followers_list' => $data_followers,
      'following_list' => $data_following,
    ]);
  }

  /**
   * Todo -> move to CAPI.
   */
  #[Route(path: '/follower/unfollow/{id}', name: 'unfollow', methods: ['DELETE'], defaults: ['id' => 0])]
  public function unfollowUser(string $id): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
    }

    if ($user->getId() === $id) {
      return new JsonResponse([], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @var User|null $user_to_unfollow */
    $user_to_unfollow = $this->user_manager->find($id);
    if (null === $user_to_unfollow) {
      return new JsonResponse([], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $user->removeFollowing($user_to_unfollow);
    $this->user_manager->updateUser($user);
    $existing_notifications = $this->notification_repo->getFollowNotificationForUser($user_to_unfollow, $user);
    foreach ($existing_notifications as $notification) {
      $this->notification_service->removeNotification($notification);
    }

    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
  }

  /**
   * Todo -> move to CAPI.
   */
  #[Route(path: '/follower/follow/{id}', name: 'follow', methods: ['POST'], defaults: ['id' => 0])]
  public function followUser(string $id): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
    }

    if ($user->getId() === $id) {
      return new JsonResponse([], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @var User|null $user_to_follow */
    $user_to_follow = $this->user_manager->find($id);
    if (null === $user_to_follow) {
      return new JsonResponse([], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $followings = $user->getFollowing();
    if ($followings->contains($user_to_follow)) {
      return new JsonResponse([], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $user->addFollowing($user_to_follow);
    $this->user_manager->updateUser($user);
    $this->addFollowNotificationIfNotExists($user, $user_to_follow);

    return new JsonResponse([], Response::HTTP_OK);
  }

  private function addFollowNotificationIfNotExists(User $user, User $user_to_follow): void
  {
    $notification_exists = false;
    $user_notifications = $this->notification_repo->findBy(['user' => $user_to_follow], ['id' => 'DESC']);
    foreach ($user_notifications as $notification) {
      if ($notification instanceof FollowNotification
        && $notification->getUser()->getId() === $user_to_follow->getId()
        && $notification->getFollower()->getId() === $user->getId()) {
        $notification_exists = true;
        break;
      }
    }

    if (!$notification_exists) {
      $notification = new FollowNotification($user_to_follow, $user);
      $this->notification_service->addNotification($notification);
    }
  }
}
