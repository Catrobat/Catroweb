<?php

declare(strict_types=1);

namespace Tests\PhpUnit\User\Notification;

use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\NewProgramNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\User;
use App\System\Mail\MailerAdapter;
use App\User\Notification\EmailDigestService;
use App\User\Notification\EmailNotificationPreference;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(EmailDigestService::class)]
class EmailDigestServiceTest extends TestCase
{
  public function testGroupNotificationsGroupsByType(): void
  {
    $user = $this->createStub(User::class);

    $comment = $this->createStub(CommentNotification::class);
    $like = $this->createStub(LikeNotification::class);
    $follow = $this->createStub(FollowNotification::class);
    $newProgram = $this->createStub(NewProgramNotification::class);
    $remix = $this->createStub(RemixNotification::class);
    $generic = new CatroNotification($user, 'title', 'msg');

    $service = new EmailDigestService(
      $this->createStub(EntityManagerInterface::class),
      $this->createStub(MailerAdapter::class),
      $this->createStub(TranslatorInterface::class),
      new NullLogger(),
    );

    $grouped = $service->groupNotifications([
      $comment, $like, $follow, $newProgram, $remix, $generic,
    ]);

    $this->assertCount(1, $grouped['comment']);
    $this->assertCount(1, $grouped['reaction']);
    $this->assertCount(2, $grouped['follower']);
    $this->assertCount(1, $grouped['remix']);
    $this->assertCount(1, $grouped['other']);
    $this->assertArrayNotHasKey('moderation', $grouped);
  }

  public function testGroupNotificationsEmptyArray(): void
  {
    $service = new EmailDigestService(
      $this->createStub(EntityManagerInterface::class),
      $this->createStub(MailerAdapter::class),
      $this->createStub(TranslatorInterface::class),
      new NullLogger(),
    );

    $grouped = $service->groupNotifications([]);

    $this->assertSame([], $grouped);
  }

  public function testSendDigestsReturnsZeroWhenNoUsers(): void
  {
    $query = $this->createStub(Query::class);
    $query->method('getResult')->willReturn([]);

    $qb = $this->createStub(QueryBuilder::class);
    $qb->method('select')->willReturnSelf();
    $qb->method('from')->willReturnSelf();
    $qb->method('join')->willReturnSelf();
    $qb->method('where')->willReturnSelf();
    $qb->method('andWhere')->willReturnSelf();
    $qb->method('setParameter')->willReturnSelf();
    $qb->method('setMaxResults')->willReturnSelf();
    $qb->method('getQuery')->willReturn($query);

    $em = $this->createStub(EntityManagerInterface::class);
    $em->method('createQueryBuilder')->willReturn($qb);

    $service = new EmailDigestService(
      $em,
      $this->createStub(MailerAdapter::class),
      $this->createStub(TranslatorInterface::class),
      new NullLogger(),
    );

    $sent = $service->sendDigests(EmailNotificationPreference::DAILY);

    $this->assertSame(0, $sent);
  }
}
