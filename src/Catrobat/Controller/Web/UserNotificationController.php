<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\Services\StatisticsService;
use App\Catrobat\StatusCode;
use App\Entity\CatroNotification;
use App\Entity\CommentNotification;
use App\Entity\FollowNotification;
use App\Entity\LikeNotification;
use App\Entity\NewProgramNotification;
use App\Entity\RemixManager;
use App\Entity\RemixNotification;
use App\Entity\User;
use App\Repository\CatroNotificationRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserNotificationController extends AbstractController
{
  private StatisticsService $statistics;

  public function __construct(StatisticsService $statistics_service)
  {
    $this->statistics = $statistics_service;
  }

  /**
   * @Route("/notifications/{notification_type}", name="user_notifications", methods={"GET"})
   */
  public function userNotificationsAction(string $notification_type,
                                          CatroNotificationRepository $notification_repo): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $catro_user_notifications = $notification_repo->findBy(['user' => $user], ['id' => 'DESC']);
    $avatars = [];
    $new_notifications = [];
    $old_notifications = [];

    foreach ($catro_user_notifications as $notification)
    {
      $found_notification = false;

      $user = null;
      if ('allNotifications' === $notification_type)
      {
        $found_notification = true;
      }
      if ($notification instanceof LikeNotification && 'likes' === $notification_type)
      {
        $found_notification = true;

        $user = $notification->getLikeFrom();
      }
      elseif ($notification instanceof CommentNotification && 'comments' === $notification_type)
      {
        $found_notification = true;
      }
      elseif ($notification instanceof NewProgramNotification && 'followers' === $notification_type)
      {
        $found_notification = true;
        $user = $notification->getProgram()->getUser();
      }
      elseif ($notification instanceof FollowNotification && 'followers' === $notification_type)
      {
        $found_notification = true;
        $user = $notification->getFollower();
      }
      elseif ($notification instanceof RemixNotification)
      {
        if ('remix' === $notification_type)
        {
          $found_notification = true;
        }
        $user = $notification->getRemixFrom();
      }
      if (null !== $user)
      {
        $avatar = $user->getAvatar();
        if ($avatar)
        {
          $avatars[$notification->getId()] = $avatar;
        }
      }
      if ($notification->getSeen() && $found_notification)
      {
        $old_notifications[$notification->getId()] = $notification;
      }
      elseif (!$notification->getSeen() && $found_notification)
      {
        $new_notifications[$notification->getId()] = $notification;
      }
    }
    $response = $this->render('Notifications/usernotifications.html.twig', [
      'oldNotifications' => $old_notifications,
      'newNotifications' => $new_notifications,
      'avatars' => $avatars,
      'notificationType' => $notification_type,
    ]);

    $response->headers->set('Cache-Control', 'no-store, must-revalidate, max-age=0');
    $response->headers->set('Pragma', 'no-cache');

    return $response;
  }

  /**
   * @Route("/notifications/notifications/count", name="user_notifications_count", methods={"GET"})
   */
  public function userNotificationsCountAction(CatroNotificationRepository $notification_repo,
                                               RemixManager $remix_manager): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }

    $catro_user_notifications_all = $notification_repo->findBy(['user' => $user]);
    $likes = 0;
    $followers = 0;
    $comments = 0;
    $remixes = 0;
    $all = 0;
    foreach ($catro_user_notifications_all as $notification)
    {
      /** @var CatroNotification $notification */
      if ($notification->getSeen())
      {
        continue;
      }

      if ($notification instanceof LikeNotification)
      {
        ++$likes;
      }
      elseif ($notification instanceof FollowNotification || $notification instanceof NewProgramNotification)
      {
        ++$followers;
      }
      elseif ($notification instanceof CommentNotification)
      {
        ++$comments;
      }
      elseif ($notification instanceof RemixNotification)
      {
        ++$remixes;
      }

      ++$all;
    }

    $unseen_remixed_program_data = $remix_manager->getUnseenRemixProgramsDataOfUser($user);

    return new JsonResponse([
      'count' => ['all-notifications' => $all,
        'all-notifications-dropdown' => $all,
        'likes' => $likes,
        'followers' => $followers,
        'comments' => $comments,
        'remixes' => $remixes, ],
      'statusCode' => 200,
    ]);
  }

  /**
   * @Route("/notifications/{notification_type}/seen", name="user_notifications_seen", methods={"GET"})
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function userNotificationsSeenAction(string $notification_type,
                                              CatroNotificationRepository $notification_repo,
                                              CatroNotificationService $notification_service,
                                              RemixManager $remix_manager): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }
    $catro_user_notifications = $notification_repo->findBy(['user' => $user]);
    $notifications_seen = [];
    foreach ($catro_user_notifications as $notification)
    {
      /** @var CatroNotification $notification */
      if ('likes' === $notification_type && $notification instanceof LikeNotification
        || 'followers' === $notification_type && $notification instanceof FollowNotification
        || 'followers' === $notification_type && $notification instanceof NewProgramNotification
        || 'comments' === $notification_type && $notification instanceof CommentNotification
        || 'remixes' === $notification_type && $notification instanceof RemixNotification
        || 'allNotifications' === $notification_type)
      {
        $notifications_seen[$notification->getID()] = $notification;
      }
    }
    $notification_service->markSeen($notifications_seen);
    $remix_manager->markAllUnseenRemixRelationsOfUserAsSeen($user);

    return new JsonResponse(['success' => true]);
  }

  /**
   * @Route("/notifications/{notification_type}/deleteAll", name="delete_all_notifications", methods={"GET"})
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function userNotificationsDeleteAllAction(string $notification_type,
                                                   CatroNotificationRepository $notification_repo, CatroNotificationService $notification_service,
                                                   RemixManager $remix_manager): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }
    $catro_user_notifications = $notification_repo->findBy(['user' => $user]);
    $notifications_to_delete = [];
    foreach ($catro_user_notifications as $notification)
    {
      /** @var CatroNotification $notification */
      if ('likes' === $notification_type && $notification instanceof LikeNotification
        || 'followers' === $notification_type && $notification instanceof FollowNotification
        || 'followers' === $notification_type && $notification instanceof NewProgramNotification
        || 'comments' === $notification_type && $notification instanceof CommentNotification
        || 'remixes' === $notification_type && $notification instanceof RemixNotification
        || 'allNotifications' === $notification_type)
      {
        $notifications_to_delete[$notification->getID()] = $notification;
      }
    }
    $notification_service->deleteNotifications($notifications_to_delete);
    $remix_manager->markAllUnseenRemixRelationsOfUserAsSeen($user);

    return new JsonResponse(['success' => true]);
  }

  /**
   * @Route("/notification/ancestor/{ancestor_id}/descendant/{descendant_id}",
   * name="see_user_notification", methods={"GET"})
   *
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws Exception
   */
  public function seeUserNotificationAction(Request $request, string $ancestor_id, string $descendant_id,
                                            RemixManager $remix_manager): RedirectResponse
  {
    $user = $this->getUser();
    if (null === $user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $remix_relation = $remix_manager->findCatrobatRelation($ancestor_id, $descendant_id);
    if (null === $remix_relation)
    {
      throw $this->createNotFoundException('Unable to find Remix relation entity.');
    }
    if ($user->getId() !== $remix_relation->getAncestor()->getUser()->getId())
    {
      throw $this->createNotFoundException('You are not allowed to update Remix relation entity '.'because you do not own the parent program.');
    }

    $referrer = $request->headers->get('referer');
    $locale = strtolower($request->getLocale());
    $this->statistics->createClickStatistics($request, 'rec_remix_notification',
      $ancestor_id, $descendant_id, null, null,
      $referrer, $locale, false);

    $remix_manager->markRemixRelationAsSeen($remix_relation);

    return $this->redirectToRoute('program', [
      'id' => $descendant_id,
      'rec_by_page_id' => RecommendedPageId::NOTIFICATION_CENTER_PAGE,
      'rec_by_program_id' => $ancestor_id,
    ]);
  }

  /**
   * @Route("/notifications/markasread/{notification_id}", name="catro_notification_mark_as_read",
   * requirements={"notification_id": "\d+"}, defaults={"notification_id": null}, methods={"GET"})
   */
  public function markCatroNotificationAsRead(int $notification_id,
                                              CatroNotificationService $notification_service,
                                              CatroNotificationRepository $notification_repo): JsonResponse
  {
    $user = $this->getUser();
    if (null === $user)
    {
      return JsonResponse::create(['success' => false, 'message' => 'User not logged in']);
    }
    $notification_seen = $notification_repo->findOneBy(['id' => $notification_id, 'user' => $user]);
    if (null === $notification_seen)
    {
      return new JsonResponse(['success' => false,
        'message' => 'Notification not found or doesnt belong to user', ]);
    }
    $notification_service->markSeen([$notification_seen]);

    return new JsonResponse(['success' => true]);
  }

  /**
   * @Route("/notifications/deleteNotification/{notification_id}", name="delete_notification",
   * requirements={"notification_id": "\d+"}, defaults={"notification_id": null}, methods={"GET"})
   */
  public function userNotificationDeleteAction(int $notification_id,
                                               CatroNotificationService $notification_service,
                                               CatroNotificationRepository $notification_repo): JsonResponse
  {
    $user = $this->getUser();
    if (null === $user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }
    $delete_notification_ = $notification_repo->findOneBy(['id' => $notification_id,
      'user' => $user, ]);
    if (null === $delete_notification_)
    {
      return new JsonResponse(['success' => false,
        'message' => 'Notification not found or doesnt belong to user', ]);
    }

    $notification_service->deleteNotifications([$delete_notification_]);

    return new JsonResponse(['success' => true]);
  }
}
