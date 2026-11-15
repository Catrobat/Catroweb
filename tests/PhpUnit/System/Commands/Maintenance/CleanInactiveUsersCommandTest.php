<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\User\User;
use App\System\Commands\Maintenance\CleanInactiveUsersCommand;
use App\System\Mail\MailerAdapter;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(CleanInactiveUsersCommand::class)]
final class CleanInactiveUsersCommandTest extends KernelTestCase
{
  private EntityManagerInterface $em;

  private UserDataFixtures $users;

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
    $due = $this->user('-3 years', '-3 years', '-31 days');
    $returned = $this->user('-3 years', '-1 day', '-31 days');
    $staff = $this->user('-3 years', null, null, ['ROLE_MODERATOR']);
    $creator = $this->user('-3 years', null);
    $projects = self::getContainer()->get(ProjectDataFixtures::class);
    $this->assertInstanceOf(ProjectDataFixtures::class, $projects);
    $owner = $this->em->find(User::class, $creator);
    $this->assertInstanceOf(User::class, $owner);
    $projects->insertProject(['name' => 'kept '.uniqid(), 'owned by' => $owner->getUsername()]);

    $mailed = [];
    $mailer = $this->createStub(MailerAdapter::class);
    $mailer->method('send')->willReturnCallback(static function (string $to) use (&$mailed): bool {
      $mailed[] = $to;

      return true;
    });
    $user_manager = self::getContainer()->get(UserManager::class);
    $this->assertInstanceOf(UserManager::class, $user_manager);
    $translator = self::getContainer()->get(TranslatorInterface::class);
    $this->assertInstanceOf(TranslatorInterface::class, $translator);
    $command = new CleanInactiveUsersCommand($this->em, $user_manager, $mailer, $translator, new NullLogger());

    $dry = new CommandTester($command);
    $dry->execute(['--dry-run' => true]);
    $this->assertStringContainsString('accounts would be deleted', $dry->getDisplay());
    $this->assertSame([], $mailed);

    new CommandTester($command)->execute([]);
    $this->em->clear();

    $this->assertNotNull($this->em->find(User::class, $idle)?->getInactivityWarnedAt());
    $this->assertContains($idle.'@example.org', $mailed);
    $this->assertNull($this->em->find(User::class, $due));
    $this->assertNull($this->em->find(User::class, $returned)?->getInactivityWarnedAt());
    foreach ([$active, $returned, $staff, $creator] as $kept) {
      $user = $this->em->find(User::class, $kept);
      $this->assertInstanceOf(User::class, $user);
      $this->assertNotContains($user->getEmail(), $mailed);
    }
  }

  /** @param list<string> $roles */
  private function user(string $created, ?string $last_login, ?string $warned = null, array $roles = []): string
  {
    $name = 'idle'.uniqid();
    $user = $this->users->insertUser(['name' => $name, 'email' => $name.'@example.org']);
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
