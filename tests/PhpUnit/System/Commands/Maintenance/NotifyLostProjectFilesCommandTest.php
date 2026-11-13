<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\User\Notifications\ProjectFilesLostNotification;
use App\DB\Entity\User\User;
use App\System\Commands\Maintenance\NotifyLostProjectFilesCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(NotifyLostProjectFilesCommand::class)]
class NotifyLostProjectFilesCommandTest extends TestCase
{
  /** @var list<ProjectFilesLostNotification> notices persisted so far (the "table") */
  private array $notices = [];

  /** @var list<array{id: string, name: string, user_id: string}> */
  private array $broken_rows = [
    ['id' => 'p1', 'name' => 'Alpha', 'user_id' => 'u1'],
    ['id' => 'p2', 'name' => 'Beta', 'user_id' => 'u1'],
    ['id' => 'p3', 'name' => 'Gamma', 'user_id' => 'u2'],
  ];

  public function testCreatesOneNoticePerOwnerListingTheirProjects(): void
  {
    $this->runCommand();

    self::assertCount(2, $this->notices);
    self::assertSame(['p1' => 'Alpha', 'p2' => 'Beta'], $this->notices[0]->getProjects());
    self::assertSame('u1', $this->notices[0]->getUser()->getId());
    self::assertSame(['p3' => 'Gamma'], $this->notices[1]->getProjects());
    self::assertSame('u2', $this->notices[1]->getUser()->getId());
  }

  public function testDryRunCreatesNothing(): void
  {
    $tester = $this->runCommand(['--dry-run' => true]);

    self::assertSame([], $this->notices);
    self::assertStringContainsString('Would notify 2 owner(s) about 3 project(s)', $tester->getDisplay());
  }

  public function testRunningTwiceDoesNotNotifyTwice(): void
  {
    $this->runCommand();
    $tester = $this->runCommand();

    self::assertCount(2, $this->notices);
    self::assertStringContainsString('Notified 0 owner(s)', $tester->getDisplay());
  }

  public function testOnlyAnnouncesProjectsMissingFromEarlierNotices(): void
  {
    $this->runCommand();
    $this->broken_rows[] = ['id' => 'p4', 'name' => 'Delta', 'user_id' => 'u1'];
    $this->runCommand();

    self::assertCount(3, $this->notices);
    self::assertSame(['p4' => 'Delta'], $this->notices[2]->getProjects());
  }

  /**
   * @param array<string, mixed> $options
   */
  private function runCommand(array $options = []): CommandTester
  {
    $query = $this->createStub(Query::class);
    $query->method('getArrayResult')->willReturnCallback(fn (): array => $this->broken_rows);

    $qb = $this->createStub(QueryBuilder::class);
    foreach (['select', 'from', 'where', 'andWhere', 'orderBy'] as $method) {
      $qb->method($method)->willReturnSelf();
    }
    $qb->method('getQuery')->willReturn($query);

    $repository = $this->createStub(EntityRepository::class);
    $repository->method('findAll')->willReturnCallback(fn (): array => $this->notices);

    $pending = [];
    $em = $this->createStub(EntityManagerInterface::class);
    $em->method('createQueryBuilder')->willReturn($qb);
    $em->method('getRepository')->willReturn($repository);
    $em->method('find')->willReturnCallback(static function (string $class, string $id): User {
      $user = new User();
      $user->setId($id);

      return $user;
    });
    $em->method('persist')->willReturnCallback(static function (object $entity) use (&$pending): void {
      if ($entity instanceof ProjectFilesLostNotification) {
        $pending[] = $entity;
      }
    });
    $em->method('flush')->willReturnCallback(function () use (&$pending): void {
      array_push($this->notices, ...$pending);
      $pending = [];
    });

    $tester = new CommandTester(new NotifyLostProjectFilesCommand($em));
    $tester->execute($options);
    $tester->assertCommandIsSuccessful();

    return $tester;
  }
}
