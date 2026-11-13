<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Notifications;

use App\Api\Services\Notifications\NotificationsResponseManager;
use App\DB\Entity\User\Notifications\ProjectFilesLostNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\Moderation\ContentVisibilityManager;
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
class ProjectFilesLostNotificationResponseTest extends TestCase
{
  public function testListsAtMostTenProjectNamesAndCountsTheRest(): void
  {
    $projects = [];
    for ($i = 1; $i <= 12; ++$i) {
      $projects['id-'.$i] = 'Game '.$i;
    }

    $message = $this->renderMessage(new ProjectFilesLostNotification(new User(), $projects));

    self::assertStringStartsWith('catro-notifications.project-files-lost.message|12|"Game 1", ', $message);
    self::assertStringContainsString('"Game 10" catro-notifications.project-files-lost.more|2', $message);
    self::assertStringNotContainsString('Game 11', $message);
  }

  public function testListsAllNamesWhenThereAreFew(): void
  {
    $message = $this->renderMessage(new ProjectFilesLostNotification(new User(), ['a' => 'Alpha', 'b' => 'Beta']));

    self::assertSame('catro-notifications.project-files-lost.message|2|"Alpha", "Beta"', $message);
  }

  private function renderMessage(ProjectFilesLostNotification $notification): string
  {
    $translator = $this->createStub(TranslatorInterface::class);
    $translator->method('trans')->willReturnCallback(
      static fn (string $id, array $parameters): string => implode('|', [$id, ...array_values($parameters)])
    );

    $manager = new NotificationsResponseManager(
      $translator,
      $this->createStub(SerializerInterface::class),
      $this->createStub(CacheItemPoolInterface::class),
      $this->createStub(NotificationRepository::class),
      $this->createStub(ContentVisibilityManager::class),
      $this->createStub(UserAvatarService::class),
    );

    $data = $manager->createNotificationListResponse([$notification], false, new User())->getData();
    self::assertNotNull($data);
    $item = $data[0];
    self::assertSame('project', $item->getType());

    return (string) $item->getMessage();
  }
}
