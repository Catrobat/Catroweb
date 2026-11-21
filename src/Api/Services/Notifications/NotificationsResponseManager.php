<?php

declare(strict_types=1);

namespace App\Api\Services\Notifications;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Traits\KeysetCursorTrait;
use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\Notifications\NewProjectNotification;
use App\DB\Entity\User\Notifications\ProjectDeletedNotification;
use App\DB\Entity\User\Notifications\ProjectExpiringNotification;
use App\DB\Entity\User\Notifications\ProjectFilesLostNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\Notifications\StudioCommentNotification;
use App\DB\Entity\User\Notifications\StudioDeletionWarningNotification;
use App\DB\Entity\User\Notifications\StudioJoinRequestNotification;
use App\DB\Entity\User\Notifications\StudioProjectNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportCategory;
use App\DB\Entity\Moderation\ContentModerationAction;
use App\Moderation\AutoModerationService;
use App\Moderation\ContentVisibilityManager;
use App\Moderation\ReportProcessor;
use App\System\Commands\Maintenance\PurgeHiddenContentCommand;
use App\User\UserAvatarService;
use OpenAPI\Server\Model\NotificationListResponse;
use OpenAPI\Server\Model\NotificationResponse;
use OpenAPI\Server\Model\NotificationsCountResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Sonata\AdminBundle\Form\Type\Operator\StringOperatorType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationsResponseManager extends AbstractResponseManager
{
  use KeysetCursorTrait;

  private const int LOST_FILES_LISTED_NAMES = 10;

  /** Moderation notifications that only admins get: they link to the moderation queue instead of the content. */
  private const array ADMIN_REVIEW_ACTIONS = [
    ContentModerationAction::ACTION_AUTO_HIDDEN,
    ContentModerationAction::ACTION_BRIGADING_SUSPECTED,
    AutoModerationService::ACTION_REVIEW_REQUESTED,
  ];

  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    \Psr\Cache\CacheItemPoolInterface $cache,
    private readonly NotificationRepository $notification_repository,
    private readonly ContentVisibilityManager $content_visibility_manager,
    private readonly UserAvatarService $user_avatar_service,
    private readonly UrlGeneratorInterface $url_generator,
  ) {
    parent::__construct($translator, $serializer, $cache);
  }

  /** The text fields a moderation reason can name (catro-notifications.moderation-reason.text.*). */
  private const array MODERATION_REASON_FIELDS = ['name', 'description', 'credits', 'about', 'currently_working_on', 'username'];

  /**
   * Kid-friendly and without detector internals: which images or texts, or else the report category.
   *
   * @param array{category?: string, screenshot?: bool, cover?: bool, images?: list<string>, fields?: list<string>}|null $reason
   */
  private function moderationReason(?array $reason): string
  {
    $parts = [];
    // ponytail: the first 5 names, 40 characters each; the admin queue lists them all.
    $images = $reason['images'] ?? [];
    if ([] !== $images) {
      $names = array_map(static fn (string $name): string => mb_strimwidth($name, 0, 40, '…'), array_slice($images, 0, 5));
      $parts[] = $this->trans('catro-notifications.moderation-reason.image', ['%count%' => count($images), '%names%' => implode(', ', $names).(count($images) > 5 ? ', …' : '')]);
    }

    foreach (['screenshot', 'cover'] as $image) {
      if ($reason[$image] ?? false) {
        $parts[] = $this->trans('catro-notifications.moderation-reason.'.$image);
      }
    }

    foreach (array_intersect($reason['fields'] ?? [], self::MODERATION_REASON_FIELDS) as $field) {
      $parts[] = $this->trans('catro-notifications.moderation-reason.text.'.$field);
    }

    $category = ReportCategory::tryFrom($reason['category'] ?? '');
    if ([] === $parts && null !== $category) {
      $parts[] = $this->trans('catro-notifications.moderation-reason.category.'.$category->value);
    }

    return implode(' ', $parts);
  }

  public function createNotificationsCountResponse(User $user): NotificationsCountResponse
  {
    $counts = $this->notification_repository->getUnseenCounts($user);

    return new NotificationsCountResponse($counts);
  }

  /**
   * @param CatroNotification[] $notifications
   */
  public function createNotificationListResponse(array $notifications, bool $has_more, User $user): NotificationListResponse
  {
    $response_items = [];

    foreach ($notifications as $notification) {
      $item = $this->createNotificationResponse($notification, $user);
      if ($item instanceof NotificationResponse) {
        $response_items[] = $item;
      }
    }

    $next_cursor = null;
    if ($has_more && [] !== $notifications) {
      $last_notification = end($notifications);
      $next_cursor = $this->encodeDateKeysetCursor($last_notification->getCreatedAt(), (string) $last_notification->getId());
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
        'project' => $notification->getProject()?->getId(),
        'project_name' => $notification->getProject()?->getName(),
        'avatar' => $this->user_avatar_service->getVariants($notification->getLikeFrom()),
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
        'avatar' => $this->user_avatar_service->getVariants($notification->getFollower()),
        'message' => $this->trans('catro-notifications.follow.message'),
      ]);
    }

    if ($notification instanceof NewProjectNotification) {
      if ($notification->getProject()?->getUser() === $user) {
        return null;
      }

      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'follow',
        'seen' => $notification->getSeen(),
        'from' => $notification->getProject()?->getUser()?->getId(),
        'from_name' => $notification->getProject()?->getUser()?->getUserIdentifier(),
        'project' => $notification->getProject()?->getId(),
        'project_name' => $notification->getProject()?->getName(),
        'avatar' => $this->user_avatar_service->getVariants($notification->getProject()?->getUser()),
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
        'project' => $notification->getComment()?->getProject()?->getId(),
        'project_name' => $notification->getComment()?->getProject()?->getName(),
        'avatar' => $this->user_avatar_service->getVariants($notification->getComment()?->getUser()),
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
        'avatar' => $this->user_avatar_service->getVariants($notification->getRemixFrom()),
        'remixed_project' => $notification->getProject()?->getId(),
        'remixed_project_name' => $notification->getProject()?->getName(),
        'message' => $this->trans('catro-notifications.remix.message'),
      ]);
    }

    if ($notification instanceof ModerationNotification) {
      $message = $notification->getMessage();
      $action = $notification->getModerationAction();
      $is_warning = in_array($action, [ReportProcessor::NOTIFICATION_ADMIN_WARNING, ReportProcessor::NOTIFICATION_ADMIN_HIDE_WARNING], true);
      if ('' === $message && $is_warning) {
        $message = $this->trans('catro-notifications.moderation-warning.message');
      } elseif ('' === $message && ReportProcessor::NOTIFICATION_ADMIN_HIDDEN === $action) {
        $message = $this->trans('catro-notifications.moderation-hidden.message');
      }

      $reason = $this->moderationReason($notification->getModerationReason());
      if ('' !== $reason) {
        $message .= ' '.$reason;
      }

      // Only hide_warn and accept hide; said while the content is still hidden (catro:moderation:purge-hidden deletes it).
      $hidden_type = ContentType::tryFrom((string) $notification->getContentType());
      $hidden_id = $notification->getContentId();
      if (in_array($action, [ReportProcessor::NOTIFICATION_ADMIN_HIDE_WARNING, ReportProcessor::NOTIFICATION_ADMIN_HIDDEN], true)
        && $hidden_type instanceof ContentType && null !== $hidden_id
        && $this->content_visibility_manager->isContentHidden($hidden_type, $hidden_id)) {
        $message .= ' '.$this->trans('catro-notifications.moderation-warning.deletion', ['%days%' => PurgeHiddenContentCommand::DAYS]);
      }

      $response = [
        'id' => $notification->getId(),
        'type' => 'moderation',
        'seen' => $notification->getSeen(),
        'message' => $message,
      ];

      $content_type = $notification->getContentType();
      $content_id = $notification->getContentId();

      // Owners get auto_hidden too; only admins can open the queue.
      if (null !== $content_id && $user->hasRole('ROLE_ADMIN') && in_array($action, self::ADMIN_REVIEW_ACTIONS, true)) {
        $response['review_url'] = $this->url_generator->generate('admin_moderation_reports_list', [
          'filter' => ['content_id' => ['type' => StringOperatorType::TYPE_EQUAL, 'value' => $content_id]],
        ]);
      }

      if ('project' === $content_type && null !== $content_id) {
        $response['project'] = $content_id;
        $response['project_name'] = $this->content_visibility_manager->getContentName(ContentType::Project, $content_id);
      } elseif ('comment' === $content_type && null !== $content_id) {
        $response['project'] = $this->content_visibility_manager->getCommentProjectId($content_id);
        $response['project_name'] = $this->content_visibility_manager->getCommentProjectName($content_id);
      }

      return new NotificationResponse($response);
    }

    if ($notification instanceof StudioJoinRequestNotification) {
      $action = $notification->getAction() ?? 'accepted';
      $translationKey = 'accepted' === $action
        ? 'catro-notifications.studio-join-request.accepted'
        : 'catro-notifications.studio-join-request.declined';

      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'studio',
        'seen' => $notification->getSeen(),
        'from' => $notification->getAdminUser()?->getId(),
        'from_name' => $notification->getAdminUser()?->getUserIdentifier(),
        'avatar' => $this->user_avatar_service->getVariants($notification->getAdminUser()),
        'studio' => $notification->getStudio()?->getId(),
        'message' => $this->trans($translationKey, [
          '%studio_name%' => $notification->getStudio()?->getName() ?? '',
        ]),
      ]);
    }

    if ($notification instanceof StudioCommentNotification) {
      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'studio',
        'seen' => $notification->getSeen(),
        'from' => $notification->getCommentUser()?->getId(),
        'from_name' => $notification->getCommentUser()?->getUserIdentifier(),
        'avatar' => $this->user_avatar_service->getVariants($notification->getCommentUser()),
        'studio' => $notification->getStudio()?->getId(),
        'message' => $this->trans('catro-notifications.studio-comment.message', [
          '%user_link%' => '%user_link%',
          '%studio_name%' => $notification->getStudio()?->getName() ?? '',
        ]),
      ]);
    }

    if ($notification instanceof StudioProjectNotification) {
      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'studio',
        'seen' => $notification->getSeen(),
        'from' => $notification->getProjectUser()?->getId(),
        'from_name' => $notification->getProjectUser()?->getUserIdentifier(),
        'project' => $notification->getProject()?->getId(),
        'project_name' => $notification->getProject()?->getName(),
        'avatar' => $this->user_avatar_service->getVariants($notification->getProjectUser()),
        'studio' => $notification->getStudio()?->getId(),
        'message' => $this->trans('catro-notifications.studio-project.message', [
          '%user_link%' => '%user_link%',
          '%program_link%' => '%program_link%',
          '%studio_name%' => $notification->getStudio()?->getName() ?? '',
        ]),
      ]);
    }

    if ($notification instanceof ProjectExpiringNotification) {
      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'project',
        'seen' => $notification->getSeen(),
        'project' => $notification->getProject()?->getId(),
        'project_name' => $notification->getProject()?->getName(),
        'message' => $this->trans('catro-notifications.project-expiring.message', [
          '%program_link%' => '%program_link%',
          '%days%' => (string) ($notification->getExpiryDays() ?? 0),
        ]),
      ]);
    }

    if ($notification instanceof ProjectDeletedNotification) {
      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'project',
        'seen' => $notification->getSeen(),
        'project_name' => $notification->getProjectName(),
        'message' => $this->trans('catro-notifications.project-deleted.message', [
          '%project_name%' => $notification->getProjectName() ?? '',
        ]),
      ]);
    }

    if ($notification instanceof StudioDeletionWarningNotification) {
      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'studio',
        'seen' => $notification->getSeen(),
        'studio' => $notification->getStudio()?->getId(),
        'message' => $this->trans('catro-notifications.studio-deletion-warning.message', [
          '%studio_name%' => $notification->getStudioName(),
          '%days%' => (string) StudioDeletionWarningNotification::DAYS_UNTIL_DELETION,
        ]),
      ]);
    }

    if ($notification instanceof ProjectFilesLostNotification) {
      $names = array_values($notification->getProjects());
      $listed = implode(', ', array_map(static fn (string $name): string => '"'.$name.'"', array_slice($names, 0, self::LOST_FILES_LISTED_NAMES)));
      $more = count($names) - self::LOST_FILES_LISTED_NAMES;
      if ($more > 0) {
        $listed .= ' '.$this->trans('catro-notifications.project-files-lost.more', ['%count%' => (string) $more]);
      }

      return new NotificationResponse([
        'id' => $notification->getId(),
        'type' => 'project',
        'seen' => $notification->getSeen(),
        'message' => $this->trans('catro-notifications.project-files-lost.message', [
          '%count%' => (string) count($names),
          '%projects%' => $listed,
        ]),
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
