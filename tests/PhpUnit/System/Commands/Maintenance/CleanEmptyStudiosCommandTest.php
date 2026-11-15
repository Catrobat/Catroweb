<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\Notifications\StudioDeletionWarningNotification;
use App\DB\Entity\User\User;
use App\Studio\StudioManager;
use App\System\Commands\Maintenance\CleanEmptyStudiosCommand;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(CleanEmptyStudiosCommand::class)]
final class CleanEmptyStudiosCommandTest extends KernelTestCase
{
  private EntityManagerInterface $em;

  private StudioManager $studios;

  private User $admin;

  #[\Override]
  protected function setUp(): void
  {
    self::bootKernel();
    $em = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $em);
    $this->em = $em;
    $studios = self::getContainer()->get(StudioManager::class);
    $this->assertInstanceOf(StudioManager::class, $studios);
    $this->studios = $studios;
    $users = self::getContainer()->get(UserDataFixtures::class);
    $this->assertInstanceOf(UserDataFixtures::class, $users);
    $this->admin = $users->insertUser(['name' => 'studioadmin'.uniqid()]);
  }

  public function testWarnsThenDeletesEmptyInactiveStudiosAndKeepsLiveOnes(): void
  {
    $inactive = $this->studio('-100 days');
    $young = $this->studio('-10 days');
    $due = $this->studio('-200 days', '-31 days');
    $revived = $this->studio('-200 days', '-31 days');
    $projects = self::getContainer()->get(ProjectDataFixtures::class);
    $this->assertInstanceOf(ProjectDataFixtures::class, $projects);
    $project = $projects->insertProject(['name' => 'studio '.uniqid(), 'owned by' => $this->admin->getUsername()]);
    $revived_studio = $this->find($revived);
    $this->assertInstanceOf(Studio::class, $revived_studio);
    $this->studios->addProjectToStudio($this->admin, $revived_studio, $project);

    $command = new CleanEmptyStudiosCommand($this->em, $this->studios, new NullLogger());
    $dry = new CommandTester($command);
    $dry->execute(['--dry-run' => true]);
    $this->assertStringContainsString('studios would be warned', $dry->getDisplay());
    $this->assertNotNull($this->find($due));

    new CommandTester($command)->execute([]);
    $this->em->clear();

    $this->assertNotNull($this->find($inactive)?->getDeletionWarnedAt());
    $this->assertNull($this->find($young)?->getDeletionWarnedAt());
    $this->assertNull($this->find($due));
    $revived_studio = $this->find($revived);
    $this->assertInstanceOf(Studio::class, $revived_studio);
    $this->assertNull($revived_studio->getDeletionWarnedAt());
    $warnings = $this->em->getRepository(StudioDeletionWarningNotification::class)->findBy(['user' => $this->admin->getId()]);
    $this->assertSame([$inactive], array_map(static fn (StudioDeletionWarningNotification $n): ?string => $n->getStudio()?->getId(), $warnings));

    new CommandTester($command)->execute([]);
    $this->assertCount(1, $this->em->getRepository(StudioDeletionWarningNotification::class)->findBy(['user' => $this->admin->getId()]), 'warned once');
  }

  /** A studio whose creation and activities all lie $age back, optionally warned $warned ago. */
  private function studio(string $age, ?string $warned = null): string
  {
    $id = (string) $this->studios->createStudio($this->admin, 'empty '.uniqid(), 'test')->getId();
    $connection = $this->em->getConnection();
    $at = new \DateTime($age)->format('Y-m-d H:i:s');
    $connection->executeStatement('UPDATE studio SET created_on = ?, updated_on = NULL, deletion_warned_at = ? WHERE id = ?', [$at, null === $warned ? null : new \DateTime($warned)->format('Y-m-d H:i:s'), $id]);
    $connection->executeStatement('UPDATE studio_activity SET created_on = ? WHERE studio = ?', [$at, $id]);
    $this->em->clear();
    $admin = $this->em->find(User::class, $this->admin->getId());
    $this->assertInstanceOf(User::class, $admin);
    $this->admin = $admin;

    return $id;
  }

  private function find(string $id): ?Studio
  {
    return $this->em->find(Studio::class, $id);
  }
}
