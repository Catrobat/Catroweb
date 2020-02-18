<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\CatroNotificationService;
use App\Entity\FollowNotification;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use App\Repository\CatroNotificationRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\Error;

/**
 * Class FollowerController.
 */
class FollowerController extends AbstractController
{
  /**
   * @Route("/follower", name="catrobat_follower", methods={"GET"})
   *
   * @param GuidType $id
   *
   * @throws Error
   *
   * @return Response
   */
  public function followerAction(Request $request, ProgramManager $program_manager, UserManager $user_manager, $id = 0)
  {
    /**
     * @var User
     */
    $user = null;
    $my_profile = false;

    if ((0 === $id) || ($this->getUser() && $this->getUser()->getId() === $id))
    {
      $user = $this->getUser();
      $my_profile = true;
      $program_count = count($program_manager->getUserPrograms($id));
    }
    else
    {
      $user = $user_manager->find($id);
      $program_count = count($program_manager->getPublicUserPrograms($id));
    }

    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    \Locale::setDefault(substr($request->getLocale(), 0, 2));
    try
    {
      $country = Countries::getName(strtoupper($user->getCountry()));
    }
    catch (MissingResourceException $e)
    {
      $country = '';
    }
    $followerCount = $user->getFollowers()->count();
    $followingCount = $user->getFollowing()->count();

    $criteria = Criteria::create()
      ->orderBy(['username' => Criteria::ASC])
      ->setFirstResult($request->get('page') * $request->get('pageSize'))
      ->setMaxResults($request->get('pageSize'))
    ;

    $followersCollection = $user->getFollowers();
    $followingCollection = $user->getFollowing();

    $followersCollection->first();
    $followingCollection->first();
    $followers = $followersCollection->matching($criteria)->toArray();
    $following = $followingCollection->matching($criteria)->toArray();

    $data_followers = [];
    foreach ($followers as $user)
    {
      $followerCountry = '';
      try
      {
        $followerCountry = Countries::getName(strtoupper($user->getCountry()));
      }
      catch (MissingResourceException $e)
      {
        $followerCountry = '';
      }
      array_push($data_followers, [
        'username' => $user->getUsername(),
        'id' => $user->getId(),
        'avatar' => $user->getAvatar(),
        'projects' => count($user->getPrograms()),
        'country' => $followerCountry,
        'profile' => $user,
      ]);
    }

    $data_following = [];
    foreach ($following as $user)
    {
      $followingCountry = '';
      try
      {
        $followingCountry = Countries::getName(strtoupper($user->getCountry()));
      }
      catch (MissingResourceException $e)
      {
        $followingCountry = '';
      }

      array_push($data_following, [
        'username' => $user->getUsername(),
        'id' => $user->getId(),
        'avatar' => $user->getAvatar(),
        'projects' => count($user->getPrograms()),
        'country' => $followingCountry,
        'profile' => $user,
      ]);
    }

    return $this->render('Followers/followers.html.twig', [
      'profile' => $user,
      'program_count' => $program_count,
      'follower_count' => $followerCount,
      'following_count' => $followingCount,
      'country' => $country,
      'username' => $user->getUsername(),
      'myProfile' => $my_profile,
      'followers_list' => $data_followers,
      'following_list' => $data_following,
    ]);
  }

  /**
   * @Route("/follower/unfollow/{id}", name="unfollow", methods={"GET"}, defaults={"id": 0})
   *
   * @param GuidType $id
   *
   * @return JsonResponse
   */
  public function unfollowUser($id, UserManager $user_manager)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return new JsonResponse(['success' => false, 'message' => 'Please login']);
    }

    if (0 === $id)
    {
      return new JsonResponse(['success' => false, 'message' => 'Cannot follow yourself']);
    }

    /**
     * @var User
     */
    $userToUnfollow = $user_manager->find($id);
    $user->removeFollowing($userToUnfollow);
    $user_manager->updateUser($user);

    return new JsonResponse(['success' => true]);
  }

  /**
   * @Route("/follower/follow/{id}", name="follow", methods={"GET"}, defaults={"id": 0})
   *
   * @param $id
   *
   * @throws ORMException
   * @throws OptimisticLockException
   *
   * @return JsonResponse
   */
  public function followUser($id, UserManager $user_manager, CatroNotificationService $notification_service,
                             CatroNotificationRepository $notification_repo)
  {
    /**
     * @var User
     */
    $user = $this->getUser();
    if (!$user)
    {
      return new JsonResponse(['success' => false, 'message' => 'Please login']);
    }

    if (0 === $id || $id === $user->getId())
    {
      return new JsonResponse(['success' => false, 'message' => 'Cannot follow yourself']);
    }

    /**
     * @var User
     */
    $notification_check = true;
    $userToFollow = $user_manager->find($id);
    $user->addFollowing($userToFollow);
    $user_manager->updateUser($user);
    $catro_user_notifications = $notification_repo->findByUser($userToFollow, ['id' => 'DESC']);
    foreach ($catro_user_notifications as $notification)
    {
      if (($notification instanceof FollowNotification))
      {
        if (($notification->getUser()->getId() === $userToFollow->getId()) and
          ($notification->getFollower()->getId() === $user->getId()))
        {
          $notification_check = false;
          break;
        }
      }
    }
    if ($notification_check)
    {
      $notification = new FollowNotification($userToFollow, $user);
      $notification_service->addNotification($notification);
    }

    return new JsonResponse(['success' => true]);
  }
}
