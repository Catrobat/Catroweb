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

  /** @var list<array{id: string, name: string, uploaded_at: \DateTime, user_id: string}> */
  private array $broken_rows;

  #[\Override]
  protected function setUp(): void
  {
    $this->broken_rows = [
      $this->row('p1', 'Alpha', 'u1'),
      $this->row('p2', 'Beta', 'u1'),
      $this->row('p3', 'Gamma', 'u2'),
    ];
  }

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
    $this->broken_rows[] = $this->row('p4', 'Delta', 'u1');
    $this->runCommand();

    self::assertCount(3, $this->notices);
    self::assertSame(['p4' => 'Delta'], $this->notices[2]->getProjects());
  }

  public function testReAnnouncesAProjectLostAgainAfterARestore(): void
  {
    $this->runCommand();
    // p1 was re-uploaded after the notice and has been flagged again since
    $this->broken_rows[0]['uploaded_at'] = new \DateTime('+1 minute');
    $this->runCommand();

    self::assertCount(3, $this->notices);
    self::assertSame(['p1' => 'Alpha'], $this->notices[2]->getProjects());
  }

  public function testSplitsLargeListsOverSeveralNotices(): void
  {
    $this->broken_rows = [];
    for ($i = 0; $i < 450; ++$i) {
      $this->broken_rows[] = $this->row('id-'.$i, 'Spiel '.$i.' ä', 'u1');
    }

    $this->runCommand();

    self::assertSame([200, 200, 50], array_map(static fn (ProjectFilesLostNotification $n): int => count($n->getProjects()), $this->notices));
    self::assertStringContainsString('ä', $this->notices[0]->getMessage());
  }

  /**
   * @return array{id: string, name: string, uploaded_at: \DateTime, user_id: string}
   */
  private function row(string $id, string $name, string $user_id): array
  {
    return ['id' => $id, 'name' => $name, 'uploaded_at' => new \DateTime('-30 days'), 'user_id' => $user_id];
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
