<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Notifications;

use App\Api\Services\Notifications\NotificationsResponseManager;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\Moderation\ContentVisibilityManager;
use App\Moderation\ReportProcessor;
use App\User\UserAvatarService;
use OpenAPI\Server\Service\SerializerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(NotificationsResponseManager::class)]
class ModerationWarningNotificationResponseTest extends TestCase
{
  public function testEmptyWarningGetsTranslatedDefault(): void
  {
    self::assertSame(
      'catro-notifications.moderation-warning.message',
      $this->renderMessage(ReportProcessor::NOTIFICATION_ADMIN_WARNING, ''),
    );
  }

  public function testModeratorWarningTextIsKept(): void
  {
    self::assertSame('Custom text', $this->renderMessage(ReportProcessor::NOTIFICATION_ADMIN_WARNING, 'Custom text'));
  }

  public function testOtherModerationNotificationsAreNotDefaulted(): void
  {
    self::assertSame('', $this->renderMessage('report_accepted', ''));
  }

  public function testHideWarningOnHiddenContentAnnouncesTheDeletion(): void
  {
    self::assertSame(
      'catro-notifications.moderation-warning.message catro-notifications.moderation-warning.deletion 14',
      $this->renderMessage(ReportProcessor::NOTIFICATION_ADMIN_HIDE_WARNING, '', true),
    );
  }

  public function testPlainWarningNeverAnnouncesADeletion(): void
  {
    self::assertSame('Custom text', $this->renderMessage(ReportProcessor::NOTIFICATION_ADMIN_WARNING, 'Custom text', true));
  }

  public function testHideWarningOnRestoredContentAnnouncesNothing(): void
  {
    self::assertSame('Custom text', $this->renderMessage(ReportProcessor::NOTIFICATION_ADMIN_HIDE_WARNING, 'Custom text'));
  }

  private function renderMessage(string $moderation_action, string $message, bool $hidden = false): string
  {
    $translator = $this->createStub(TranslatorInterface::class);
    $translator->method('trans')->willReturnCallback(static fn (string $id, array $parameters = []): string => trim($id.' '.implode(' ', $parameters)));
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('isContentHidden')->willReturn($hidden);

    $manager = new NotificationsResponseManager(
      $translator,
      $this->createStub(SerializerInterface::class),
      $this->createStub(CacheItemPoolInterface::class),
      $this->createStub(NotificationRepository::class),
      $visibility,
      $this->createStub(UserAvatarService::class),
    );

    $notification = new ModerationNotification(new User(), 'studio', 'studio-id', $moderation_action, $message);
    $data = $manager->createNotificationListResponse([$notification], false, new User())->getData();
    self::assertNotNull($data);

    return (string) $data[0]->getMessage();
  }
}
