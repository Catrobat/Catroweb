<?php

namespace App\Api\Services\Notifications;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\ResponseCache\ResponseCacheManager;
use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\NewProjectNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use OpenAPI\Server\Model\NotificationsCountResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationsResponseManager extends AbstractResponseManager
{
  public function __construct(TranslatorInterface $translator,
    SerializerInterface $serializer,
    ResponseCacheManager $response_cache_manager,
    private readonly NotificationRepository $notification_repository)
  {
    parent::__construct($translator, $serializer, $response_cache_manager);
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
      } elseif ($notification instanceof FollowNotification || $notification instanceof NewProjectNotification) {
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
}
