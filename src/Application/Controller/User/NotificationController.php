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
    $all_notifications = [];
    $follower_notifications = [];
    $comment_notifications = [];
    $reaction_notifications = [];
    $remix_notifications = [];
    $notification_instance = [];
    $redirect_array = [];
    foreach ($notifications as $notification) {
      $notification_instance[$notification->getId()] = 'other';
      $redirect_array[$notification->getId()] = 'other';

      if ($notification instanceof LikeNotification) {
        if ($notification->getLikeFrom() !== $user) {
          $all_notifications[$notification->getId()] = $notification;
          $reaction_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'reaction';
          $redirect_array[$notification->getId()] = $notification->getProgram()?->getId() ?? 'other';
        }
      } elseif ($notification instanceof CommentNotification) {
        if ($notification->getComment()?->getUser() !== $user) {
          $all_notifications[$notification->getId()] = $notification;
          $comment_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'comment';
          $redirect_array[$notification->getId()] = $notification->getComment()?->getProgram()?->getId() ?? 'other';
        }
      } elseif ($notification instanceof NewProgramNotification) {
        if ($notification->getProgram()?->getUser() !== $user) {
          $all_notifications[$notification->getId()] = $notification;
          $follower_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'program';
          $redirect_array[$notification->getId()] = $notification->getProgram()?->getId() ?? 'other';
        }
      } elseif ($notification instanceof FollowNotification) {
        if ($notification->getFollower() !== $user) {
          $all_notifications[$notification->getId()] = $notification;
          $follower_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'follow';
          $redirect_array[$notification->getId()] = $notification->getFollower()->getId();
        }
      } elseif ($notification instanceof RemixNotification) {
        if ($notification->getRemixFrom() !== $user) {
          $all_notifications[$notification->getId()] = $notification;
          $remix_notifications[$notification->getId()] = $notification;
          $notification_instance[$notification->getId()] = 'remix';
          $redirect_array[$notification->getId()] = $notification->getRemixProgram()?->getId() ?? 'other';
        }
      } else {
        $all_notifications[$notification->getId()] = $notification;
      }
    }

    return $this->render('User/Notification/NotificationsPage.html.twig', [
      'allNotifications' => $all_notifications,
      'followerNotifications' => $follower_notifications,
      'commentNotifications' => $comment_notifications,
      'reactionNotifications' => $reaction_notifications,
      'remixNotifications' => $remix_notifications,
      'instance' => $notification_instance,
      'redirect' => $redirect_array,
      'allNotificationsCount' => count($all_notifications),
      'followNotificationCount' => count($follower_notifications),
      'reactionNotificationCount' => count($reaction_notifications),
      'commentNotificationCount' => count($comment_notifications),
      'remixNotificationCount' => count($remix_notifications),
    ]);
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
  public function fetchMoreNotifications(NotificationRepository $notification_repo, TranslatorInterface $translator, int $limit, int $offset, string $type): JsonResponse
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
      if ($notification instanceof LikeNotification) {
        if ($notification->getLikeFrom() === $user) {
          continue;
        }

        $fetched_notifications[] = [
          'id' => $notification->getId(),
          'from' => $notification->getLikeFrom()->getId(),
          'from_name' => $notification->getLikeFrom()->getUserIdentifier(),
          'program' => $notification->getProgram()->getId(),
          'program_name' => $notification->getProgram()->getName(),
          'avatar' => $notification->getLikeFrom()->getAvatar(),
          'remixed_program' => null,
          'remixed_program_name' => null,
          'type' => 'reaction',
          'message' => $translator->trans('catro-notifications.like.message', [], 'catroweb'),
          'seen' => $notification->getSeen(),
        ];
      } elseif ($notification instanceof FollowNotification) {
        if ($notification->getFollower() === $user) {
          continue;
        }
        $fetched_notifications[] = [
          'id' => $notification->getId(),
          'from' => $notification->getFollower()->getId(),
          'from_name' => $notification->getFollower()->getUserIdentifier(),
          'program' => null,
          'program_name' => null,
          'avatar' => $notification->getFollower()->getAvatar(),
          'remixed_program' => null,
          'remixed_program_name' => null,
          'type' => 'follow',
          'message' => $translator->trans('catro-notifications.follow.message', [], 'catroweb'),
          'seen' => $notification->getSeen(),
        ];
      } elseif ($notification instanceof NewProgramNotification) {
        if ($notification->getProgram()->getUser() === $user) {
          continue;
        }
        $fetched_notifications[] = [
          'id' => $notification->getId(),
          'from' => $notification->getProgram()->getUser()->getId(),
          'from_name' => $notification->getProgram()->getUser()->getUserIdentifier(),
          'program' => $notification->getProgram()->getId(),
          'program_name' => $notification->getProgram()->getName(),
          'avatar' => $notification->getProgram()->getUser()->getAvatar(),
          'remixed_program' => null,
          'remixed_program_name' => null,
          'type' => 'program',
          'message' => $translator->trans('catro-notifications.project-upload.message', [], 'catroweb'),
          'seen' => $notification->getSeen(),
        ];
      } elseif ($notification instanceof CommentNotification) {
        if ($notification->getComment()->getUser() === $user) {
          continue;
        }
        $fetched_notifications[] = [
          'id' => $notification->getId(),
          'from' => $notification->getComment()->getUser()->getId(),
          'from_name' => $notification->getComment()->getUser()->getUserIdentifier(),
          'program' => $notification->getComment()->getProgram()->getId(),
          'program_name' => $notification->getComment()->getProgram()->getName(),
          'avatar' => $notification->getComment()->getUser()->getAvatar(),
          'remixed_program' => null,
          'remixed_program_name' => null,
          'type' => 'comment',
          'message' => $translator->trans('catro-notifications.comment.message', [], 'catroweb'),
          'seen' => $notification->getSeen(),
        ];
      } elseif ($notification instanceof RemixNotification) {
        if ($notification->getRemixFrom() === $user) {
          continue;
        }
        $fetched_notifications[] = [
          'id' => $notification->getId(),
          'from' => $notification->getRemixFrom()->getId(),
          'from_name' => $notification->getRemixFrom()->getUserIdentifier(),
          'program' => $notification->getRemixProgram()->getId(),
          'program_name' => $notification->getRemixProgram()->getName(),
          'avatar' => $notification->getRemixFrom()->getAvatar(),
          'remixed_program' => $notification->getProgram()->getId(),
          'remixed_program_name' => $notification->getProgram()->getName(),
          'type' => 'remix',
          'message' => $translator->trans('catro-notifications.remix.message', [], 'catroweb'),
          'seen' => $notification->getSeen(),
        ];
      } elseif ('all' === $type) {
        $fetched_notifications[] = [
          'id' => $notification->getId(),
          'from' => null,
          'from_name' => null,
          'program' => 'other',
          'program_name' => null,
          'avatar' => null,
          'remixed_program' => null,
          'remixed_program_name' => null,
          'type' => 'other',
          'message' => $notification->getMessage(),
          'seen' => $notification->getSeen(),
        ];
      }
    }

    return new JsonResponse([
      'fetched-notifications' => $fetched_notifications,
    ], Response::HTTP_OK);
  }
}
