<?php

declare(strict_types=1);

namespace App\Api\Services\Notifications;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\NewProgramNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use OpenAPI\Server\Model\NotificationListResponse;
use OpenAPI\Server\Model\NotificationResponse;
use OpenAPI\Server\Model\NotificationsCountResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationsResponseManager extends AbstractResponseManager
{
  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    \Psr\Cache\CacheItemPoolInterface|\Symfony\Contracts\Cache\CacheInterface $cache,
    private readonly NotificationRepository $notification_repository,
  ) {
    parent::__construct($translator, $serializer, $cache);
  }

  public function createNotificationsCountResponse(User $user): NotificationsCountResponse
  {
    $notifications_all = $this->notification_repository->findBy(['user' => $user]);
    $likes = 0;
    $followers = 0;
    $comments = 0;
    $remixes = 0;
    $all = 0;
    foreach ($notifications_all as $notification) {
      /** @var CatroNotification $notification */
      if ($notification->getSeen()) {
        continue;
      }

      if ($notification instanceof LikeNotification) {
        ++$likes;
      } elseif ($notification instanceof FollowNotification || $notification instanceof NewProgramNotification) {
        ++$followers;
      } elseif ($notification instanceof CommentNotification) {
        ++$comments;
      } elseif ($notification instanceof RemixNotification) {
        ++$remixes;
      }

      ++$all;
    }

    return new NotificationsCountResponse([
      'total' => $all,
      'like' => $likes,
      'follower' => $followers,
      'comment' => $comments,
      'remix' => $remixes,
    ]);
  }

  /**
   * @param CatroNotification[] $notifications
   */
  public function createNotificationListResponse(array $notifications, bool $has_more, User $user): NotificationListResponse
  {
    $response_items = [];

    foreach ($notifications as $notification) {
      $item = $this->createNotificationResponse($notification, $user);
      if (null !== $item) {
        $response_items[] = $item;
      }
    }

    $next_cursor = null;
    if ($has_more && [] !== $notifications) {
      $last_notification = end($notifications);
      $next_cursor = base64_encode((string) $last_notification->getId());
    }

    return new NotificationListResponse([
      'data' => $response_items,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ]);
  }

  private function createNotificationResponse(CatroNotification $notification, User $user): ?NotificationResponse
  {
    if ($notification instanceof LikeNotification) {
      if ($notification->getLikeFrom() === $user) {
        return null;
      }

      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'reaction',
        'seen' => $notification->getSeen(),
        'from' => $notification->getLikeFrom()?->getId(),
        'from_name' => $notification->getLikeFrom()?->getUserIdentifier(),
        'project' => $notification->getProgram()?->getId(),
        'project_name' => $notification->getProgram()?->getName(),
        'avatar' => $notification->getLikeFrom()?->getAvatar(),
        'message' => $this->trans('catro-notifications.like.message'),
      ]);
    }

    if ($notification instanceof FollowNotification) {
      if ($notification->getFollower() === $user) {
        return null;
      }

      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'follow',
        'seen' => $notification->getSeen(),
        'from' => $notification->getFollower()->getId(),
        'from_name' => $notification->getFollower()->getUserIdentifier(),
        'avatar' => $notification->getFollower()->getAvatar(),
        'message' => $this->trans('catro-notifications.follow.message'),
      ]);
    }

    if ($notification instanceof NewProgramNotification) {
      if ($notification->getProgram()?->getUser() === $user) {
        return null;
      }

      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'follow',
        'seen' => $notification->getSeen(),
        'from' => $notification->getProgram()?->getUser()?->getId(),
        'from_name' => $notification->getProgram()?->getUser()?->getUserIdentifier(),
        'project' => $notification->getProgram()?->getId(),
        'project_name' => $notification->getProgram()?->getName(),
        'avatar' => $notification->getProgram()?->getUser()?->getAvatar(),
        'message' => $this->trans('catro-notifications.project-upload.message'),
      ]);
    }

    if ($notification instanceof CommentNotification) {
      if ($notification->getComment()?->getUser() === $user) {
        return null;
      }

      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'comment',
        'seen' => $notification->getSeen(),
        'from' => $notification->getComment()?->getUser()?->getId(),
        'from_name' => $notification->getComment()?->getUser()?->getUserIdentifier(),
        'project' => $notification->getComment()?->getProgram()?->getId(),
        'project_name' => $notification->getComment()?->getProgram()?->getName(),
        'avatar' => $notification->getComment()?->getUser()?->getAvatar(),
        'message' => $this->trans('catro-notifications.comment.message'),
      ]);
    }

    if ($notification instanceof RemixNotification) {
      if ($notification->getRemixFrom() === $user) {
        return null;
      }

      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'remix',
        'seen' => $notification->getSeen(),
        'from' => $notification->getRemixFrom()?->getId(),
        'from_name' => $notification->getRemixFrom()?->getUserIdentifier(),
        'project' => $notification->getRemixProgram()?->getId(),
        'project_name' => $notification->getRemixProgram()?->getName(),
        'avatar' => $notification->getRemixFrom()?->getAvatar(),
        'remixed_project' => $notification->getProgram()?->getId(),
        'remixed_project_name' => $notification->getProgram()?->getName(),
        'message' => $this->trans('catro-notifications.remix.message'),
      ]);
    }

    return new NotificationResponse([
      'id' => $notification->getId(),
      'type' => 'other',
      'seen' => $notification->getSeen(),
      'message' => $notification->getMessage(),
    ]);
  }
}
