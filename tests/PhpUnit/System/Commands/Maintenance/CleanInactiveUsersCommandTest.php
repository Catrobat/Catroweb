<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\Studio\StudioManager;
use App\System\Commands\Maintenance\CleanInactiveUsersCommand;
use App\System\Mail\EmailBudgetManager;
use App\System\Mail\MailerAdapter;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(CleanInactiveUsersCommand::class)]
final class CleanInactiveUsersCommandTest extends KernelTestCase
{
  private EntityManagerInterface $em;

  private UserDataFixtures $users;

  /** @var list<string> addresses a warning went out to */
  private array $mailed = [];

  #[\Override]
  protected function setUp(): void
  {
    self::bootKernel();
    $em = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $em);
    $this->em = $em;
    $users = self::getContainer()->get(UserDataFixtures::class);
    $this->assertInstanceOf(UserDataFixtures::class, $users);
    $this->users = $users;
  }

  public function testWarnsIdleAccountsThenDeletesThoseThatStayedAway(): void
  {
    $idle = $this->user('-3 years', null);
    $active = $this->user('-3 years', '-1 day');
    // Its id sorts first and a project names it as approver (restricting foreign key): the run must go on.
    $blocked = $this->user('-3 years', '-3 years', '-31 days', [], '00000000-0000-4000-8000-'.substr(bin2hex(random_bytes(6)), 0, 12));
    $due = $this->user('-3 years', '-3 years', '-31 days');
    $recently_warned = $this->user('-3 years', '-3 years', '-10 days');
    $returned = $this->user('-3 years', '-1 day', '-31 days');
    $staff = $this->user('-3 years', null, null, ['ROLE_MODERATOR']);
    $creator = $this->user('-3 years', null);
    $scratch = $this->user('-3 years', null);
    $commenter = $this->user('-3 years', null);
    $member = $this->user('-3 years', null);

    $projects = self::getContainer()->get(ProjectDataFixtures::class);
    $this->assertInstanceOf(ProjectDataFixtures::class, $projects);
    $project = $projects->insertProject(['name' => 'kept '.uniqid(), 'owned by' => $this->find($creator)->getUsername()]);
    $connection = $this->em->getConnection();
    $connection->executeStatement('UPDATE program SET approved_by_user = ? WHERE id = ?', [$blocked, $project->getId()]);
    $connection->executeStatement('UPDATE fos_user SET scratch_user_id = ? WHERE id = ?', [random_int(1, 2_000_000_000), $scratch]);
    $comment = new UserComment()->setUser($this->find($commenter))->setUsername('c')->setText('hi')->setUploadDate(new \DateTime());
    $this->em->persist($comment);
    $this->em->flush();
    $studios = self::getContainer()->get(StudioManager::class);
    $this->assertInstanceOf(StudioManager::class, $studios);
    $studios->createStudio($this->find($member), 'idle studio '.uniqid(), 'test');
    $this->em->clear();

    $dry = new CommandTester($this->command());
    $dry->execute(['--dry-run' => true]);
    $this->assertStringContainsString('accounts would be deleted', $dry->getDisplay());
    $this->assertSame([], $this->mailed);

    $run = new CommandTester($this->command());
    $this->assertSame(0, $run->execute([]));
    $this->assertStringContainsString('(1 failed)', $run->getDisplay());
    $this->em->clear();

    $this->assertNotNull($this->em->find(User::class, $idle)?->getInactivityWarnedAt());
    $this->assertSame([$idle.'@example.org'], array_values(array_intersect($this->mailed, array_map(static fn (string $id): string => $id.'@example.org', [$idle, $active, $returned, $staff, $creator, $scratch, $commenter, $member]))));
    $this->assertNull($this->em->find(User::class, $due));
    $this->assertNotNull($this->em->find(User::class, $blocked), 'skipped, not fatal');
    $this->assertNotNull($this->em->find(User::class, $recently_warned)?->getInactivityWarnedAt(), 'warned 10 days ago: neither deleted nor reset');
    $this->assertNull($this->em->find(User::class, $returned)?->getInactivityWarnedAt(), 'a login after the warning cancels it');
  }

  public function testStopsWhenTheMailBudgetRunsOutAndRecordsOnlyDeliveredWarnings(): void
  {
    $first = $this->user('-3 years', null, null, [], '00000001-0000-4000-8000-'.substr(bin2hex(random_bytes(6)), 0, 12));
    $undelivered = $this->user('-3 years', null, null, [], '00000002-0000-4000-8000-'.substr(bin2hex(random_bytes(6)), 0, 12));
    $over_budget = $this->user('-3 years', null, null, [], '00000003-0000-4000-8000-'.substr(bin2hex(random_bytes(6)), 0, 12));

    // Room for exactly two mails; the provider refuses the second.
    new CommandTester($this->command(bulk_room: 2, refuse: $undelivered.'@example.org'))->execute([]);
    $this->em->clear();

    $this->assertNotNull($this->em->find(User::class, $first)?->getInactivityWarnedAt());
    $this->assertNull($this->em->find(User::class, $undelivered)?->getInactivityWarnedAt(), 'not delivered: not recorded');
    $this->assertNull($this->em->find(User::class, $over_budget)?->getInactivityWarnedAt());
    $this->assertNotContains($over_budget.'@example.org', $this->mailed);
  }

  private function command(int $bulk_room = PHP_INT_MAX, string $refuse = ''): CleanInactiveUsersCommand
  {
    $mailer = $this->createStub(MailerAdapter::class);
    $mailer->method('sendNow')->willReturnCallback(function (string $to) use ($refuse): bool {
      $this->mailed[] = $to;

      return $to !== $refuse;
    });
    $budget = $this->createStub(EmailBudgetManager::class);
    $budget->method('canSendBulk')->willReturnCallback(fn (): bool => count($this->mailed) < $bulk_room);
    $container = self::getContainer();
    $user_manager = $container->get(UserManager::class);
    $this->assertInstanceOf(UserManager::class, $user_manager);
    $translator = $container->get(TranslatorInterface::class);
    $this->assertInstanceOf(TranslatorInterface::class, $translator);
    $doctrine = $container->get(ManagerRegistry::class);
    $this->assertInstanceOf(ManagerRegistry::class, $doctrine);

    return new CleanInactiveUsersCommand($this->em, $user_manager, $mailer, $translator, new NullLogger(), $budget, $doctrine, new InMemoryTransport());
  }

  private function find(string $id): User
  {
    $user = $this->em->find(User::class, $id);
    $this->assertInstanceOf(User::class, $user);

    return $user;
  }

  /** @param list<string> $roles */
  private function user(string $created, ?string $last_login, ?string $warned = null, array $roles = [], ?string $id = null): string
  {
    $name = 'idle'.uniqid();
    $user = $this->users->insertUser(['name' => $name] + (null === $id ? [] : ['id' => $id]));
    $user->setRoles($roles);
    $this->em->flush();
    $id = (string) $user->getId();
    $at = static fn (?string $modify): ?string => null === $modify ? null : new \DateTime($modify)->format('Y-m-d H:i:s');
    $this->em->getConnection()->executeStatement(
      'UPDATE fos_user SET created_at = ?, last_login = ?, inactivity_warned_at = ?, email = ? WHERE id = ?',
      [$at($created), $at($last_login), $at($warned), $id.'@example.org', $id],
    );
    $this->em->clear();

    return $id;
  }
}
