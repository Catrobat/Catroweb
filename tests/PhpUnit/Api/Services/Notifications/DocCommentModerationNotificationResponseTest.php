<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Notifications;

use App\Api\Services\Notifications\NotificationsResponseManager;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\Docs\DocsLibrary;
use App\Moderation\ContentVisibilityManager;
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
class DocCommentModerationNotificationResponseTest extends TestCase
{
  public function testAModeratedDocsCommentLinksToItsDocsPage(): void
  {
    self::assertSame('/app/docs/tutorials/jump#comments', $this->url('tutorials/jump'));
  }

  public function testAModeratedProjectCommentHasNoUrl(): void
  {
    self::assertNull($this->url(null));
  }

  private function url(?string $doc_path): ?string
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('getCommentDocPath')->willReturn($doc_path);
    $url_generator = $this->createStub(UrlGeneratorInterface::class);
    $url_generator->method('generate')->willReturnCallback(
      static fn (string $route, array $parameters): string => '/'.$parameters['theme'].'/'.$route.'/'.$parameters['path']
    );

    $manager = new NotificationsResponseManager(
      $this->createStub(TranslatorInterface::class),
      $this->createStub(SerializerInterface::class),
      $this->createStub(CacheItemPoolInterface::class),
      $this->createStub(NotificationRepository::class),
      $visibility,
      $this->createStub(UserAvatarService::class),
      $url_generator,
      $this->createStub(DocsLibrary::class),
    );

    $notification = new ModerationNotification(new User(), 'comment', 'comment-id', 'report_accepted', 'Hidden');
    $data = $manager->createNotificationListResponse([$notification], false, new User())->getData();
    self::assertNotNull($data);

    return $data[0]->getUrl();
  }
}
