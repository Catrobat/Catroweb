<?php

declare(strict_types=1);

namespace App\Application\Controller\User;

use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\User\Achievements\AchievementManager;
use App\User\Notification\NotificationManager;
use App\User\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
  final public const int MIN_PASSWORD_LENGTH = 6;

  final public const int MAX_PASSWORD_LENGTH = 4096;

  public function __construct(
    protected ProjectManager $project_manager,
    protected UserManager $user_manager,
    protected AchievementManager $achievement_manager,
  ) {
  }

  /**
   * Overwrite for FosUser Profile Route (We don't use it!).
   */
  #[Route(path: '/user/{id}', name: 'profile', defaults: ['id' => 0], methods: ['GET'])]
  #[Route(path: '/user/}')]
  public function profile(string $id): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if ('0' === $id || ($user && $user->getId() === $id)) {
      if (is_null($user)) {
        return $this->redirectToRoute('login');
      }

      $project_count = $this->project_manager->countUserProjects($user->getId());
      $view = 'User/Profile/MyProfilePage.html.twig';
    } else {
      /** @var User|null $user */
      $user = $this->user_manager->find($id);
      if (is_null($user)) {
        return $this->redirectToRoute('index');
      }

      $project_count = $this->project_manager->countPublicUserProjects($id);
      $view = 'User/Profile/ProfilePage.html.twig';
    }

    return $this->render($view, [
      'profile' => $user,
      'project_count' => $project_count,
      'firstMail' => $user->getEmail(),
      'minPassLength' => self::MIN_PASSWORD_LENGTH,
      'maxPassLength' => self::MAX_PASSWORD_LENGTH,
      'username' => $user->getUsername(),
      'followers_list' => $this->user_manager->getMappedUserData($user->getFollowers()->toArray()),
      'following_list' => $this->user_manager->getMappedUserData($user->getFollowing()->toArray()),
      'achievements' => $this->achievement_manager->findUnlockedAchievements($user),
    ]);
  }

  #[Route(path: '/followUser/{id}', name: 'follow_user', methods: ['GET'], defaults: ['id' => 0])]
  public function followUser(string $id, UserManager $user_manager, NotificationManager $notification_service): RedirectResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return $this->redirectToRoute('login');
    }

    if ('0' === $id || $id === $user->getId()) {
      return $this->redirectToRoute('profile');
    }

    /** @var User $user_to_follow */
    $user_to_follow = $user_manager->find($id);
    $user->addFollowing($user_to_follow);
    $user_manager->updateUser($user);
    $notification = new FollowNotification($user_to_follow, $user);
    $notification_service->addNotification($notification);

    return $this->redirectToRoute('profile', ['id' => $id]);
  }

  #[Route(path: '/unfollowUser/{id}', name: 'unfollow_user', methods: ['GET'], defaults: ['id' => 0])]
  public function unfollowUser(string $id, UserManager $user_manager): RedirectResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return $this->redirectToRoute('login');
    }

    if ('0' === $id) {
      return $this->redirectToRoute('profile');
    }

    /** @var User $user_to_unfollow */
    $user_to_unfollow = $user_manager->find($id);
    $user->removeFollowing($user_to_unfollow);
    $user_manager->updateUser($user);

    return $this->redirectToRoute('profile', ['id' => $id]);
  }

  #[Route(path: '/follow/{type}', name: 'list_follow', methods: ['POST'], defaults: ['_format' => 'json'], requirements: ['type' => 'follower|follows'])]
  public function listFollow(Request $request, string $type, UserManager $user_manager): JsonResponse
  {
    $page = $request->request->getInt('page');
    $pageSize = $request->request->getInt('pageSize');
    $followCollection = null;
    $criteria = Criteria::create()
      ->orderBy(['username' => Criteria::ASC])
      ->setFirstResult($page * $pageSize)
      ->setMaxResults($pageSize)
    ;
    /** @var User|null $user */
    $user = $user_manager->find($request->request->get('id'));
    switch ($type) {
      case 'follower':
        /** @var ArrayCollection $followCollection */
        $followCollection = $user->getFollowers();
        break;
      case 'follows':
        /** @var ArrayCollection $followCollection */
        $followCollection = $user->getFollowing();
        break;
    }

    $length = $followCollection->count();
    $followCollection->first();
    $users = $followCollection->matching($criteria)->toArray();
    $data = [];
    foreach ($users as $user) {
      $data[] = [
        'username' => $user->getUsername(),
        'id' => $user->getId(),
        'avatar' => $user->getAvatar(),
      ];
    }

    return new JsonResponse(['profiles' => $data, 'maximum' => $length]);
  }
}
