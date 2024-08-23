<?php

declare(strict_types=1);

namespace App\Application\Controller\User;

use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\NewProgramNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\Project\Remix\RemixManager;
use App\User\Notification\NotificationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationController extends AbstractController
{
  final public const int load_limit = 20;

  final public const int load_offset = 0;

  #[Route(path: '/user_notifications', name: 'notifications', methods: ['GET'])]
  public function Notifications(NotificationRepository $notification_repository): Response
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
          $redirect_array[$notification->getId()] = $notification->getProgram()->getId();
        }
      } elseif ($notification instanceof CommentNotification) {
        if ($notification->getComment()->getUser() != $this->getUser()) {
          $all_notifications[$notification->getId()] = $notification;
          $comment_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'comment';
          $redirect_array[$notification->getId()] = $notification->getComment()->getProgram()->getId();
        }
      } elseif ($notification instanceof NewProgramNotification) {
        $user = $notification->getProgram()->getUser();
        if ($user != $this->getUser()) {
          $all_notifications[$notification->getId()] = $notification;
          $follower_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'program';
          $redirect_array[$notification->getId()] = $notification->getProgram()->getId();
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
          $redirect_array[$notification->getId()] = $notification->getRemixProgram()->getId();
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

      if ($user instanceof User) {
        $avatar = $user->getAvatar();
        if (null !== $avatar && '' !== $avatar && '0' !== $avatar) {
          $avatars[$notification->getId()] = $avatar;
        }
      }
    }

    $all_notifications_count = count($all_notifications);
    $follower_count = count($follower_notifications);
    $reaction_count = count($reaction_notifications);
    $comment_count = count($comment_notifications);
    $remix_count = count($remix_notifications);
    $response = $this->render('User/Notification/NotificationsPage.html.twig', [
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
          'program' => $notification->getProgram()->getId(),
          'program_name' => $notification->getProgram()->getName(),
          'avatar' => $notification->getLikeFrom()->getAvatar(),
          'remixed_program' => null,
          'remixed_program_name' => null,
          'type' => 'reaction',
          'message' => $translator->trans('catro-notifications.like.message', [], 'catroweb'),
          'seen' => $notification->getSeen(), ];

        continue;
      }

      if (($notification instanceof FollowNotification || $notification instanceof NewProgramNotification)
          && ('follow' === $type || 'all' === $type)) {
        if ($notification instanceof FollowNotification && $notification->getFollower() === $this->getUser()) {
          continue;
        }
        if ($notification instanceof NewProgramNotification && $notification->getProgram()->getUser() === $this->getUser()) {
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
            'from' => $notification->getProgram()->getUser()->getId(),
            'from_name' => $notification->getProgram()->getUser()->getUserIdentifier(),
            'program' => $notification->getProgram()->getId(),
            'program_name' => $notification->getProgram()->getName(),
            'avatar' => $notification->getProgram()->getUser()->getAvatar(),
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
          'program' => $notification->getComment()->getProgram()->getId(),
          'program_name' => $notification->getComment()->getProgram()->getName(),
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
          'program' => $notification->getRemixProgram()->getId(),
          'program_name' => $notification->getRemixProgram()->getName(),
          'avatar' => $notification->getRemixFrom()->getAvatar(),
          'remixed_program' => $notification->getProgram()->getId(),
          'remixed_program_name' => $notification->getProgram()->getName(),
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
