<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\CatroNotificationService;
use App\Entity\CatroNotification;
use App\Entity\CommentNotification;
use App\Entity\FollowNotification;
use App\Entity\LikeNotification;
use App\Entity\NewProgramNotification;
use App\Entity\RemixManager;
use App\Entity\RemixNotification;
use App\Entity\User;
use App\Repository\CatroNotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationsController extends AbstractController
{
  /**
   * @Route("/user_notifications", name="notifications", methods={"GET"})
   */
  public function NotificationsAction(CatroNotificationRepository $notification_repository, Request $request): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('login');
    }

    $catro_user_notifications = $notification_repository->findBy(['user' => $user], ['id' => 'DESC']);
    $avatars = [];
    $new_notifications = [];
    $old_notifications = [];
    $all_notifications = [];
    $follower_notifications = [];
    $comment_notifications = [];
    $reaction_notifications = [];
    $remix_notifications = [];
    $notification_instance = [];
    $redirect_array = [];
    foreach ($catro_user_notifications as $notification)
    {
      $found_notification = false;
      $user = null;
      $notification_instance[$notification->getId()] = null;
      $redirect_array[$notification->getId()] = null;

      if ($notification instanceof LikeNotification)
      {
        $found_notification = true;
        $user = $notification->getLikeFrom();
        if ($user != $this->getUser())
        {
          $all_notifications[$notification->getId()] = $notification;
          $reaction_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'reaction';
          $redirect_array[$notification->getId()] = $notification->getProgram()->getId();
        }
      }
      elseif ($notification instanceof CommentNotification)
      {
        $found_notification = true;
        if ($notification->getComment()->getUser() != $this->getUser())
        {
          $all_notifications[$notification->getId()] = $notification;
          $comment_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'comment';
          $redirect_array[$notification->getId()] = $notification->getComment()->getProgram()->getId();
        }
      }
      elseif ($notification instanceof NewProgramNotification)
      {
        $found_notification = true;
        $user = $notification->getProgram()->getUser();
        if ($user != $this->getUser())
        {
          $all_notifications[$notification->getId()] = $notification;
          $follower_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'program';
          $redirect_array[$notification->getId()] = $notification->getProgram()->getId();
        }
      }
      elseif ($notification instanceof FollowNotification)
      {
        $found_notification = true;
        $user = $notification->getFollower();
        if ($user != $this->getUser())
        {
          $all_notifications[$notification->getId()] = $notification;
          $follower_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'follow';
          $redirect_array[$notification->getId()] = $notification->getFollower()->getId();
        }
      }
      elseif ($notification instanceof RemixNotification)
      {
        $found_notification = true;
        $user = $notification->getRemixFrom();
        if ($user != $this->getUser())
        {
          $all_notifications[$notification->getId()] = $notification;
          $remix_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'remix';
          $redirect_array[$notification->getId()] = $notification->getRemixProgram()->getId();
        }
      }
      else
      {
        $all_notifications[$notification->getId()] = $notification;
      }
      if (null == $notification_instance[$notification->getId()])
      {
        $notification_instance[$notification->getId()] = 'other';
      }
      if (null == $redirect_array[$notification->getId()])
      {
        $redirect_array[$notification->getId()] = 'other';
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
    $response = $this->render('Notifications/notifications.html.twig', [
      'oldNotifications' => $old_notifications,
      'newNotifications' => $new_notifications,
      'avatars' => $avatars,
      'allNotifications' => $all_notifications,
      'followerNotifications' => $follower_notifications,
      'commentNotifications' => $comment_notifications,
      'reactionNotifications' => $reaction_notifications,
      'remixNotifications' => $remix_notifications,
      'instance' => $notification_instance,
      'redirect' => $redirect_array,
    ]);
    $response->headers->set('Cache-Control', 'no-store, must-revalidate, max-age=0');
    $response->headers->set('Pragma', 'no-cache');

    return $response;
  }

  /**
   * @Route("/user_notifications/markasread/{notification_id}", name="notification_mark_as_read",
   * requirements={"notification_id": "\d+"}, defaults={"notification_id": null}, methods={"GET"})
   * Todo -> move to CAPI
   */
  public function markNotificationAsRead(int $notification_id,
                                              CatroNotificationService $notification_service,
                                              CatroNotificationRepository $notification_repo): JsonResponse
  {
    $user = $this->getUser();
    if (null === $user)
    {
      return JsonResponse::create([], Response::HTTP_UNAUTHORIZED);
    }
    $notification_seen = $notification_repo->findOneBy(['id' => $notification_id, 'user' => $user]);
    if (null === $notification_seen)
    {
      return new JsonResponse([], Response::HTTP_NOT_FOUND);
    }
    $notification_service->markSeen([$notification_seen]);

    return new JsonResponse([], Response::HTTP_NO_CONTENT);
  }

  /**
   * @Route("/user_notifications/notifications/count", name="notifications_count", methods={"GET"})
   * Todo -> move to CAPI
   */
  public function unseenNotificationsCountAction(CatroNotificationRepository $notification_repo,
                                               RemixManager $remix_manager): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create([], Response::HTTP_UNAUTHORIZED);
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
      'all-notifications' => $all,
      'likes' => $likes,
      'followers' => $followers,
      'comments' => $comments,
      'remixes' => $remixes, ], Response::HTTP_OK);
  }

  /**
   * @Route("/user_notifications/markall", name="notifications_seen", methods={"GET"})
   */
  public function userNotificationsSeenAction(CatroNotificationRepository $notification_repo,
                                              CatroNotificationService $notification_service,
                                              RemixManager $remix_manager): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create([], Response::HTTP_UNAUTHORIZED);
    }
    $catro_user_notifications = $notification_repo->findBy(['user' => $user]);
    $notifications_seen = [];
    foreach ($catro_user_notifications as $notification)
    {
      if (!$notification)
      {
        return new JsonResponse([], Response::HTTP_NOT_FOUND);
      }
      if (!$notification->getSeen())
      {
        $notifications_seen[$notification->getID()] = $notification;
      }
    }
    $notification_service->markSeen($notifications_seen);
    $remix_manager->markAllUnseenRemixRelationsOfUserAsSeen($user);

    return new JsonResponse([], Response::HTTP_NO_CONTENT);
  }
}
