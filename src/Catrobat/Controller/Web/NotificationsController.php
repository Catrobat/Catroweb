<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\CatroNotificationService;
use App\Entity\AnniversaryNotification;
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
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationsController extends AbstractController
{
  const load_limit = 20;
  const load_offset = 0;

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

    $catro_user_notifications = $notification_repository->findBy(['user' => $user], ['id' => 'DESC'], self::load_limit, self::load_offset);
    $avatars = [];
    $all_notifications = [];
    $follower_notifications = [];
    $comment_notifications = [];
    $reaction_notifications = [];
    $remix_notifications = [];
    $notification_instance = [];
    $redirect_array = [];
    foreach ($catro_user_notifications as $notification)
    {
      $user = null;
      $notification_instance[$notification->getId()] = null;
      $redirect_array[$notification->getId()] = null;

      if ($notification instanceof LikeNotification)
      {
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
    }

    $all_notifications_count = sizeof($all_notifications);
    $follower_count = sizeof($follower_notifications);
    $reaction_count = sizeof($reaction_notifications);
    $comment_count = sizeof($comment_notifications);
    $remix_count = sizeof($remix_notifications);
    $response = $this->render('Notifications/notifications.html.twig', [
      'avatars' => $avatars,
      'allNotifications' => $all_notifications,
      'followerNotifications' => $follower_notifications,
      'commentNotifications' => $comment_notifications,
      'reactionNotifications' => $reaction_notifications,
      'remixNotifications' => $remix_notifications,
      'instance' => $notification_instance,
      'redirect' => $redirect_array,
      'allNotificationsCount' => $all_notifications_count,
      'followNotificationCount' => $follower_count,
      'reactionNotificationCount' => $reaction_count,
      'commentNotificationCount' => $comment_count,
      'remixNotificationCount' => $remix_count,
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

  /**
   * @Route("/user_notifications/fetch/{limit}/{offset}/{type}", name="notifications_fetch",
   * defaults={"limit": null, "offset": null, "type": null}, methods={"GET"})
   */
  public function fetchMoreNotifications(CatroNotificationRepository $notification_repo,
                                         CatroNotificationService $notification_service,
                                         RemixManager $remix_manager, TranslatorInterface $translator,
                                         int $limit, int $offset, string $type): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create([], Response::HTTP_UNAUTHORIZED);
    }
    $catro_user_notifications = null;
    if ('all' === $type)
    {
      $catro_user_notifications = $notification_repo->findBy(['user' => $user], ['id' => 'DESC'], $limit, $offset);
    }
    else
    {
      $catro_user_notifications = $notification_repo->findBy(['user' => $user, 'type' => $type], ['id' => 'DESC'], $limit, $offset);
    }

    $fetched_notifications = [];
    foreach ($catro_user_notifications as $notification)
    {
      if ($notification instanceof LikeNotification && ('reaction' === $type || 'all' === $type))
      {
        if (($notification->getLikeFrom() === $this->getUser()))
        {
          continue;
        }
        array_push($fetched_notifications,
          ['id' => $notification->getId(),
            'from' => $notification->getLikeFrom()->getId(),
            'from_name' => $notification->getLikeFrom()->getUsername(),
            'program' => $notification->getProgram()->getId(),
            'program_name' => $notification->getProgram()->getName(),
            'avatar' => $notification->getLikeFrom()->getAvatar(),
            'remixed_program' => null,
            'remixed_program_name' => null,
            'type' => 'reaction',
            'message' => $translator->trans('catro-notifications.like.message', [], 'catroweb'),
            'prize' => null,
            'seen' => $notification->getSeen(), ]);

        continue;
      }
      if (($notification instanceof FollowNotification || $notification instanceof NewProgramNotification)
        && ('follow' === $type || 'all' === $type))
      {
        if (($notification instanceof FollowNotification && $notification->getFollower() === $this->getUser())
          || ($notification instanceof NewProgramNotification && $notification->getProgram()->getUser() === $this->getUser()))
        {
          continue;
        }
        if ($notification instanceof FollowNotification)
        {
          array_push($fetched_notifications,
            ['id' => $notification->getId(),
              'from' => $notification->getFollower()->getId(),
              'from_name' => $notification->getFollower()->getUsername(),
              'program' => null,
              'program_name' => null,
              'avatar' => $notification->getFollower()->getAvatar(),
              'remixed_program' => null,
              'remixed_program_name' => null,
              'type' => 'follow',
              'message' => $translator->trans('catro-notifications.follow.message', [], 'catroweb'),
              'prize' => null,
              'seen' => $notification->getSeen(), ]);
        }
        else
        {
          array_push($fetched_notifications,
            ['id' => $notification->getId(),
              'from' => $notification->getProgram()->getUser()->getId(),
              'from_name' => $notification->getProgram()->getUser()->getUsername(),
              'program' => $notification->getProgram()->getId(),
              'program_name' => $notification->getProgram()->getName(),
              'avatar' => $notification->getProgram()->getUser()->getAvatar(),
              'remixed_program' => null,
              'remixed_program_name' => null,
              'type' => 'program',
              'message' => $translator->trans('catro-notifications.program-upload.message', [], 'catroweb'),
              'prize' => null,
              'seen' => $notification->getSeen(), ]);
        }
        continue;
      }
      if ($notification instanceof CommentNotification && ('comment' === $type || 'all' === $type))
      {
        if ($notification->getComment()->getUser() === $this->getUser())
        {
          continue;
        }
        array_push($fetched_notifications,
          ['id' => $notification->getId(),
            'from' => $notification->getComment()->getUser()->getId(),
            'from_name' => $notification->getComment()->getUser()->getUsername(),
            'program' => $notification->getComment()->getProgram()->getId(),
            'program_name' => $notification->getComment()->getProgram()->getName(),
            'avatar' => $notification->getComment()->getUser()->getAvatar(),
            'remixed_program' => null,
            'remixed_program_name' => null,
            'type' => 'comment',
            'message' => $translator->trans('catro-notifications.comment.message', [], 'catroweb'),
            'prize' => null,
            'seen' => $notification->getSeen(), ]);
        continue;
      }
      if ($notification instanceof RemixNotification && ('remix' === $type || 'all' === $type))
      {
        if ($notification->getRemixFrom() === $this->getUser())
        {
          continue;
        }

        array_push($fetched_notifications,
          ['id' => $notification->getId(),
            'from' => $notification->getRemixFrom()->getId(),
            'from_name' => $notification->getRemixFrom()->getUsername(),
            'program' => $notification->getRemixProgram()->getId(),
            'program_name' => $notification->getRemixProgram()->getName(),
            'avatar' => $notification->getRemixFrom()->getAvatar(),
            'remixed_program' => $notification->getProgram()->getId(),
            'remixed_program_name' => $notification->getProgram()->getName(),
            'type' => 'remix',
            'message' => $translator->trans('catro-notifications.remix.message', [], 'catroweb'),
            'prize' => null,
            'seen' => $notification->getSeen(), ]);
        continue;
      }
      if ('all' === $type)
      {
        $prize = null;
        if ($notification instanceof AnniversaryNotification)
        {
          $prize = $notification->getPrize();
        }
        array_push($fetched_notifications,
          ['id' => $notification->getId(),
            'from' => null,
            'from_name' => null,
            'program' => 'other',
            'program_name' => null,
            'avatar' => null,
            'remixed_program' => null,
            'remixed_program_name' => null,
            'type' => 'other',
            'message' => $notification->getMessage(),
            'prize' => $prize,
            'seen' => $notification->getSeen(), ]);
      }
    }

    return new JsonResponse([
      'fetched-notifications' => $fetched_notifications, ], Response::HTTP_OK);
  }
}
