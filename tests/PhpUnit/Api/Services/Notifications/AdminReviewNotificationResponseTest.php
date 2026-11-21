<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Notifications;

use App\Api\Services\Notifications\NotificationsResponseManager;
use App\DB\Entity\Moderation\ContentModerationAction;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\Moderation\AutoModerationService;
use App\Moderation\ContentVisibilityManager;
use App\Moderation\ReportProcessor;
use App\User\UserAvatarService;
use OpenAPI\Server\Service\SerializerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(NotificationsResponseManager::class)]
class AdminReviewNotificationResponseTest extends TestCase
{
  /**
   * @return array<string, array{string}>
   */
  public static function adminActions(): array
  {
    return [
      'auto-hidden' => [ContentModerationAction::ACTION_AUTO_HIDDEN],
      'flagged for review' => [AutoModerationService::ACTION_REVIEW_REQUESTED],
      'suspected brigading' => [ContentModerationAction::ACTION_BRIGADING_SUSPECTED],
    ];
  }

  #[DataProvider('adminActions')]
  public function testAdminNotificationsLinkToTheModerationQueueOfThatContent(string $action): void
  {
    self::assertSame('admin_moderation_reports_list?filter[content_id][type]=3&filter[content_id][value]=project-id', $this->reviewUrl($action));
  }

  public function testAnOwnerWhoseContentWasAutoHiddenGetsNoReviewLink(): void
  {
    self::assertNull($this->reviewUrl(ContentModerationAction::ACTION_AUTO_HIDDEN, admin: false));
  }

  public function testUserNotificationsHaveNoReviewLink(): void
  {
    self::assertNull($this->reviewUrl(ReportProcessor::NOTIFICATION_ADMIN_WARNING));
    self::assertNull($this->reviewUrl(ReportProcessor::NOTIFICATION_ADMIN_HIDDEN));
  }

  private function reviewUrl(string $action, bool $admin = true): ?string
  {
    $url_generator = $this->createStub(UrlGeneratorInterface::class);
    $url_generator->method('generate')->willReturnCallback(
      static fn (string $route, array $parameters = []): string => $route.'?'.urldecode(http_build_query($parameters))
    );

    $manager = new NotificationsResponseManager(
      $this->createStub(TranslatorInterface::class),
      $this->createStub(SerializerInterface::class),
      $this->createStub(CacheItemPoolInterface::class),
      $this->createStub(NotificationRepository::class),
      $this->createStub(ContentVisibilityManager::class),
      $this->createStub(UserAvatarService::class),
      $url_generator,
    );

    $notification = new ModerationNotification(new User(), 'project', 'project-id', $action, 'Content (project "x") was auto-hidden and needs review.');
    $recipient = new User();
    if ($admin) {
      $recipient->addRole('ROLE_ADMIN');
    }
    $data = $manager->createNotificationListResponse([$notification], false, $recipient)->getData();
    self::assertNotNull($data);

    return $data[0]->getReviewUrl();
  }
}
