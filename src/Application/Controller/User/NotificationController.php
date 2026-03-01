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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends AbstractController
{
  private const int LOAD_LIMIT = 20;

  #[Route(path: '/user_notifications', name: 'notifications', methods: ['GET'])]
  public function Notifications(NotificationRepository $notification_repository): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      return $this->redirectToRoute('login');
    }

    $notifications = $notification_repository->findBy(['user' => $user], ['id' => 'DESC'], self::LOAD_LIMIT);
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

    $last_notification = end($notifications) ?: null;
    $initial_cursor = $last_notification ? base64_encode((string) $last_notification->getId()) : null;

    return $this->render('User/Notification/NotificationsPage.html.twig', [
      'allNotifications' => $all_notifications,
      'followerNotifications' => $follower_notifications,
      'commentNotifications' => $comment_notifications,
      'reactionNotifications' => $reaction_notifications,
      'remixNotifications' => $remix_notifications,
      'instance' => $notification_instance,
      'redirect' => $redirect_array,
      'initialCursor' => $initial_cursor,
      'hasMoreNotifications' => count($notifications) >= self::LOAD_LIMIT,
    ]);
  }
}
