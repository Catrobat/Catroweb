<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\StatusCode;
use App\Entity\FollowNotification;
use App\Entity\User;
use App\Entity\UserManager;
use App\Repository\CatroNotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class FollowerController extends AbstractController
{
  private UserManager $user_manager;
  private CatroNotificationService $notification_service;
  private CatroNotificationRepository $notification_repo;

  public function __construct(UserManager $user_manager, CatroNotificationService $notification_service,
                              CatroNotificationRepository $notification_repo)
  {
    $this->user_manager = $user_manager;
    $this->notification_service = $notification_service;
    $this->notification_repo = $notification_repo;
  }

  /**
   * @Route("/follower", name="catrobat_follower", methods={"GET"})
   */
  public function followerAction(Request $request, string $id = '0'): Response
  {
    /** @var User|null */
    $user = null;

    if (('0' === $id) || ($this->getUser() && $this->getUser()->getId() === $id))
    {
      $user = $this->getUser();
    }
    else
    {
      $user = $this->user_manager->find($id);
    }

    if (null === $user)
    {
      return $this->redirectToRoute('login');
    }

    $criteria = Criteria::create()
      ->orderBy(['username' => Criteria::ASC])
      ->setFirstResult($request->get('page') * $request->get('pageSize'))
      ->setMaxResults($request->get('pageSize'))
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

    return $this->render('UserManagement/Followers/followers.html.twig', [
      'followers_list' => $data_followers,
      'following_list' => $data_following,
    ]);
  }

  /**
   * @Route("/follower/unfollow/{id}", name="unfollow", methods={"GET"}, defaults={"id": 0})
   *
   * Todo -> move to CAPI
   */
  public function unfollowUser(Request $request, string $id): JsonResponse
  {
    $csrf_token = $request->query->get('token');
    if (!$this->isCsrfTokenValid('follower', $csrf_token))
    {
      if ($request->isXmlHttpRequest())
      {
        return JsonResponse::create([
          'statusCode' => StatusCode::CSRF_FAILURE,
          'message' => 'Invalid CSRF token.',
        ], Response::HTTP_BAD_REQUEST);
      }

      throw new InvalidCsrfTokenException();
    }

    /** @var User|null $user */
    $user = $this->getUser();

    if (null === $user)
    {
      return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
    }

    if ($user->getId() === $id)
    {
      return new JsonResponse([], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @var User|null $user_to_unfollow */
    $user_to_unfollow = $this->user_manager->find($id);
    if (null === $user_to_unfollow)
    {
      return new JsonResponse([], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $user->removeFollowing($user_to_unfollow);
    $this->user_manager->updateUser($user);

    $existing_notifications = $this->notification_repo->getFollowNotificationForUser($user_to_unfollow, $user);

    foreach ($existing_notifications as $notification)
    {
      $this->notification_service->removeNotification($notification);
    }

    return new JsonResponse([], Response::HTTP_OK);
  }

  /**
   * @Route("/follower/follow/{id}", name="follow", methods={"GET"}, defaults={"id": 0})
   *
   * Todo -> move to CAPI
   */
  public function followUser(Request $request, string $id): JsonResponse
  {
    $csrf_token = $request->query->get('token');
    if (!$this->isCsrfTokenValid('follower', $csrf_token))
    {
      if ($request->isXmlHttpRequest())
      {
        return JsonResponse::create([
          'statusCode' => StatusCode::CSRF_FAILURE,
          'message' => 'Invalid CSRF token.',
        ], Response::HTTP_BAD_REQUEST);
      }

      throw new InvalidCsrfTokenException();
    }

    /** @var User|null $user */
    $user = $this->getUser();

    if (null === $user)
    {
      return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
    }

    if ($user->getId() === $id)
    {
      return new JsonResponse([], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @var User|null $user_to_follow */
    $user_to_follow = $this->user_manager->find($id);
    if (null === $user_to_follow)
    {
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
    foreach ($user_notifications as $notification)
    {
      if ($notification instanceof FollowNotification
        && $notification->getUser()->getId() === $user_to_follow->getId()
        && $notification->getFollower()->getId() === $user->getId())
      {
        $notification_exists = true;
        break;
      }
    }
    if (!$notification_exists)
    {
      $notification = new FollowNotification($user_to_follow, $user);
      $this->notification_service->addNotification($notification);
    }
  }
}
