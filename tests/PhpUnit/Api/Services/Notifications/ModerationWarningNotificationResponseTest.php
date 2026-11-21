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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

  public function testAnAcceptedReportGetsTheHiddenDefaultItsCategoryAndTheDeletion(): void
  {
    self::assertSame(
      'catro-notifications.moderation-hidden.message catro-notifications.moderation-reason.category.spam catro-notifications.moderation-warning.deletion 14',
      $this->renderMessage(ReportProcessor::NOTIFICATION_ADMIN_HIDDEN, '', true, ['category' => 'spam']),
    );
  }

  public function testFlaggedImagesAndTextsAreNamedInsteadOfTheCategory(): void
  {
    $reason = ['category' => 'sexual_content', 'images' => ['Mom', 'Dad'], 'screenshot' => true, 'fields' => ['description', 'made_up']];
    self::assertSame(
      'Text catro-notifications.moderation-reason.image 2 Mom, Dad catro-notifications.moderation-reason.screenshot'
      .' catro-notifications.moderation-reason.text.description',
      $this->renderMessage('auto_hidden', 'Text', reason: $reason),
    );
  }

  public function testAnUnknownCategoryAddsNothing(): void
  {
    self::assertSame('Text', $this->renderMessage('auto_hidden', 'Text', reason: ['category' => 'made_up']));
  }

  public function testTheReasonIsBuiltFromTheReportAndItsFlags(): void
  {
    self::assertSame(
      ['category' => 'sexual_content', 'screenshot' => true, 'images' => ['Parent Right Note Mom'], 'fields' => ['description']],
      ModerationNotification::reason('sexual_content', [
        ['type' => 'image', 'name' => '0123456789abcdef0123456789abcdef_Parent Right Note Mom.png', 'score' => 0.99],
        ['type' => 'image', 'name' => 'screen_p1.png', 'score' => 0.98],
        ['type' => 'image', 'name' => 'automatic_screenshot.png', 'score' => 0.98],
        ['type' => 'text', 'name' => '18+', 'field' => 'description'],
        ['type' => 'text', 'name' => 'nsfw'],
      ]),
    );
    self::assertSame(['category' => 'spam'], ModerationNotification::reason('spam'));
    self::assertSame(
      ['category' => 'inappropriate_content', 'cover' => true],
      ModerationNotification::reason('inappropriate_content', [['type' => 'image', 'name' => '1731234567-My-Studio-card@1x.webp', 'score' => 0.999]], true),
    );
  }

  /**
   * @param array{category?: string, screenshot?: bool, cover?: bool, images?: list<string>, fields?: list<string>}|null $reason
   */
  private function renderMessage(string $moderation_action, string $message, bool $hidden = false, ?array $reason = null): string
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
      $this->createStub(UrlGeneratorInterface::class),
    );

    $notification = new ModerationNotification(new User(), 'studio', 'studio-id', $moderation_action, $message);
    $notification->setModerationReason($reason);
    $data = $manager->createNotificationListResponse([$notification], false, new User())->getData();
    self::assertNotNull($data);

    return (string) $data[0]->getMessage();
  }
}
