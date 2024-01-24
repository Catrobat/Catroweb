<?php

namespace App\Application\Controller\User;

use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\NewProjectNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\Project\Remix\RemixManager;
use App\User\Notification\NotificationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationsController extends AbstractController
{
  final public const load_limit = 20;
  final public const load_offset = 0;

  #[Route(path: '/user_notifications', name: 'notifications', methods: ['GET'])]
  public function NotificationsAction(NotificationRepository $notification_repository, Request $request): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      return $this->redirectToRoute('login');
    }
    $notifications = $notification_repository->findBy(['user' => $user], ['id' => 'DESC'], self::load_limit, self::load_offset);
    $avatars = [];
    $all_notifications = [];
    $follower_notifications = [];
    $comment_notifications = [];
    $reaction_notifications = [];
    $remix_notifications = [];
    $notification_instance = [];
    $redirect_array = [];
    foreach ($notifications as $notification) {
      $user = null;
      $notification_instance[$notification->getId()] = null;
      $redirect_array[$notification->getId()] = null;

      if ($notification instanceof LikeNotification) {
        $user = $notification->getLikeFrom();
        if ($user != $this->getUser()) {
          $all_notifications[$notification->getId()] = $notification;
          $reaction_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'reaction';
          $redirect_array[$notification->getId()] = $notification->getProject()->getId();
        }
      } elseif ($notification instanceof CommentNotification) {
        if ($notification->getComment()->getUser() != $this->getUser()) {
          $all_notifications[$notification->getId()] = $notification;
          $comment_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'comment';
          $redirect_array[$notification->getId()] = $notification->getComment()->getProject()->getId();
        }
      } elseif ($notification instanceof NewProjectNotification) {
        $user = $notification->getProject()->getUser();
        if ($user != $this->getUser()) {
          $all_notifications[$notification->getId()] = $notification;
          $follower_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'program';
          $redirect_array[$notification->getId()] = $notification->getProject()->getId();
        }
      } elseif ($notification instanceof FollowNotification) {
        $user = $notification->getFollower();
        if ($user != $this->getUser()) {
          $all_notifications[$notification->getId()] = $notification;
          $follower_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'follow';
          $redirect_array[$notification->getId()] = $notification->getFollower()->getId();
        }
      } elseif ($notification instanceof RemixNotification) {
        $user = $notification->getRemixFrom();
        if ($user != $this->getUser()) {
          $all_notifications[$notification->getId()] = $notification;
          $remix_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'remix';
          $redirect_array[$notification->getId()] = $notification->getRemixProject()->getId();
        }
      } else {
        $all_notifications[$notification->getId()] = $notification;
      }
      if (null == $notification_instance[$notification->getId()]) {
        $notification_instance[$notification->getId()] = 'other';
      }
      if (null == $redirect_array[$notification->getId()]) {
        $redirect_array[$notification->getId()] = 'other';
      }
      if (null !== $user) {
        $avatar = $user->getAvatar();
        if ($avatar) {
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

  #[Route(path: '/notifications/count', name: 'sidebar_notifications_count', methods: ['GET'])]
  public function countNotifications(NotificationRepository $notification_repo): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (is_null($user)) {
      return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
    }

    return new JsonResponse([
      'count' => $notification_repo->count(['user' => $user, 'seen' => false]),
    ]);
  }

  #[Route(path: '/user_notifications/fetch/{limit}/{offset}/{type}', name: 'notifications_fetch', defaults: ['limit' => null, 'offset' => null, 'type' => null], methods: ['GET'])]
  public function fetchMoreNotifications(NotificationRepository $notification_repo, NotificationManager $notification_service, RemixManager $remix_manager, TranslatorInterface $translator, int $limit, int $offset, string $type): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
    }
    if ('all' === $type) {
      $notifications = $notification_repo->findBy(['user' => $user], ['id' => 'DESC'], $limit, $offset);
    } else {
      $notifications = $notification_repo->findBy(['user' => $user, 'type' => $type], ['id' => 'DESC'], $limit, $offset);
    }
    $fetched_notifications = [];
    foreach ($notifications as $notification) {
      if ($notification instanceof LikeNotification && ('reaction' === $type || 'all' === $type)) {
        if ($notification->getLikeFrom() === $this->getUser()) {
          continue;
        }
        $fetched_notifications[] = ['id' => $notification->getId(),
          'from' => $notification->getLikeFrom()->getId(),
          'from_name' => $notification->getLikeFrom()->getUserIdentifier(),
          'program' => $notification->getProject()->getId(),
          'program_name' => $notification->getProject()->getName(),
          'avatar' => $notification->getLikeFrom()->getAvatar(),
          'remixed_program' => null,
          'remixed_program_name' => null,
          'type' => 'reaction',
          'message' => $translator->trans('catro-notifications.like.message', [], 'catroweb'),
          'seen' => $notification->getSeen(), ];

        continue;
      }
      if (($notification instanceof FollowNotification || $notification instanceof NewProjectNotification)
          && ('follow' === $type || 'all' === $type)) {
        if (($notification instanceof FollowNotification && $notification->getFollower() === $this->getUser())
            || ($notification instanceof NewProjectNotification && $notification->getProject()->getUser() === $this->getUser())) {
          continue;
        }
        if ($notification instanceof FollowNotification) {
          $fetched_notifications[] = ['id' => $notification->getId(),
            'from' => $notification->getFollower()->getId(),
            'from_name' => $notification->getFollower()->getUsername(),
            'program' => null,
            'program_name' => null,
            'avatar' => $notification->getFollower()->getAvatar(),
            'remixed_program' => null,
            'remixed_program_name' => null,
            'type' => 'follow',
            'message' => $translator->trans('catro-notifications.follow.message', [], 'catroweb'),
            'seen' => $notification->getSeen(), ];
        } else {
          $fetched_notifications[] = ['id' => $notification->getId(),
            'from' => $notification->getProject()->getUser()->getId(),
            'from_name' => $notification->getProject()->getUser()->getUserIdentifier(),
            'program' => $notification->getProject()->getId(),
            'program_name' => $notification->getProject()->getName(),
            'avatar' => $notification->getProject()->getUser()->getAvatar(),
            'remixed_program' => null,
            'remixed_program_name' => null,
            'type' => 'program',
            'message' => $translator->trans('catro-notifications.project-upload.message', [], 'catroweb'),
            'seen' => $notification->getSeen(), ];
        }
        continue;
      }
      if ($notification instanceof CommentNotification && ('comment' === $type || 'all' === $type)) {
        if ($notification->getComment()->getUser() === $this->getUser()) {
          continue;
        }
        $fetched_notifications[] = ['id' => $notification->getId(),
          'from' => $notification->getComment()->getUser()->getId(),
          'from_name' => $notification->getComment()->getUser()->getUserIdentifier(),
          'program' => $notification->getComment()->getProject()->getId(),
          'program_name' => $notification->getComment()->getProject()->getName(),
          'avatar' => $notification->getComment()->getUser()->getAvatar(),
          'remixed_program' => null,
          'remixed_program_name' => null,
          'type' => 'comment',
          'message' => $translator->trans('catro-notifications.comment.message', [], 'catroweb'),
          'seen' => $notification->getSeen(), ];
        continue;
      }
      if ($notification instanceof RemixNotification && ('remix' === $type || 'all' === $type)) {
        if ($notification->getRemixFrom() === $this->getUser()) {
          continue;
        }

        $fetched_notifications[] = ['id' => $notification->getId(),
          'from' => $notification->getRemixFrom()->getId(),
          'from_name' => $notification->getRemixFrom()->getUserIdentifier(),
          'program' => $notification->getRemixProject()->getId(),
          'program_name' => $notification->getRemixProject()->getName(),
          'avatar' => $notification->getRemixFrom()->getAvatar(),
          'remixed_program' => $notification->getProject()->getId(),
          'remixed_program_name' => $notification->getProject()->getName(),
          'type' => 'remix',
          'message' => $translator->trans('catro-notifications.remix.message', [], 'catroweb'),
          'seen' => $notification->getSeen(), ];
        continue;
      }
      if ('all' === $type) {
        $fetched_notifications[] = ['id' => $notification->getId(),
          'from' => null,
          'from_name' => null,
          'program' => 'other',
          'program_name' => null,
          'avatar' => null,
          'remixed_program' => null,
          'remixed_program_name' => null,
          'type' => 'other',
          'message' => $notification->getMessage(),
          'seen' => $notification->getSeen(), ];
      }
    }

    return new JsonResponse([
      'fetched-notifications' => $fetched_notifications, ], Response::HTTP_OK);
  }
}
