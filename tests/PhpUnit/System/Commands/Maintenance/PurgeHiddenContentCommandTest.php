<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\DB\Enum\ReportState;
use App\Storage\StorageLifecycleService;
use App\Studio\StudioManager;
use App\System\Commands\Maintenance\HoldContentCommand;
use App\System\Commands\Maintenance\PurgeHiddenContentCommand;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(PurgeHiddenContentCommand::class)]
#[CoversClass(HoldContentCommand::class)]
final class PurgeHiddenContentCommandTest extends KernelTestCase
{
  private EntityManagerInterface $em;

  private StudioManager $studios;

  private User $owner;

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
    $this->owner = $this->user();
  }

  public function testDeletesModeratedContentAfterTheDeadlineOnly(): void
  {
    $due = $this->hiddenProject('-15 days', '-15 days');
    $young = $this->hiddenProject('-13 days', '-13 days');
    $hidden_recently = $this->hiddenProject('-2 days', '-15 days');
    $pending_report = $this->hiddenProject('-15 days', '-15 days');
    $this->report('project', $pending_report, ReportState::New);
    $pending_appeal = $this->hiddenProject('-15 days', '-15 days');
    $this->appeal('project', $pending_appeal);
    $unreviewed = $this->hiddenProject('-15 days');
    $this->report('project', $unreviewed, ReportState::New);
    $restored = $this->hiddenProject('-15 days', '-15 days');
    $this->em->getConnection()->executeStatement('UPDATE program SET auto_hidden = 0 WHERE id = ?', [$restored]);
    $held = $this->hiddenProject('-15 days', '-15 days');
    $this->hold('project', $held);
    $released = $this->hiddenProject('-15 days', '-15 days');
    $this->hold('project', $released);
    $this->hold('project', $released, true);
    $comment = $this->comment($this->project());
    $this->em->getConnection()->executeStatement('UPDATE user_comment SET auto_hidden = 1, auto_hidden_at = ? WHERE id = ?', [$this->at('-15 days'), $comment]);
    $this->report('comment', $comment, ReportState::Accepted, '-15 days');

    $this->purge();

    $this->assertNull($this->em->find(Project::class, $due));
    $this->assertNull($this->em->find(Project::class, $released), 'a released hold no longer protects');
    $this->assertNull($this->em->find(UserComment::class, $comment));
    foreach (['13 days' => $young, 'hidden 2 days' => $hidden_recently, 'pending report' => $pending_report, 'pending appeal' => $pending_appeal, 'never reviewed' => $unreviewed, 'restored' => $restored, 'held' => $held] as $case => $id) {
      $this->assertNotNull($this->em->find(Project::class, $id), $case.': kept');
    }
  }

  public function testBannedAccountLosesItsContentButKeepsItsRow(): void
  {
    $banned = $this->user();
    $project = $this->project($banned);
    $comment = $this->comment($this->project(), $banned);
    $own_studio = (string) $this->studios->createStudio($banned, 'own '.uniqid(), 'x')->getId();
    $shared_studio = $this->studios->createStudio($banned, 'shared '.uniqid(), 'x');
    $this->studios->addAdminToStudio($this->owner, $shared_studio);
    $shared_studio_id = (string) $shared_studio->getId();
    $this->ban($banned, '-15 days');

    $recently_banned = $this->user();
    $recent_project = $this->project($recently_banned);
    $this->ban($recently_banned, '-13 days');

    $appealing = $this->user();
    $appealing_project = $this->project($appealing);
    $this->ban($appealing, '-15 days');
    $this->appeal('user', (string) $appealing->getId());

    $this->purge();

    $this->assertNull($this->em->find(Project::class, $project));
    $this->assertNull($this->em->find(UserComment::class, $comment));
    $this->assertNull($this->em->find(Studio::class, $own_studio));
    $this->assertNotNull($this->em->find(Studio::class, $shared_studio_id), 'another admin keeps the studio');
    $user = $this->em->find(User::class, $banned->getId());
    $this->assertInstanceOf(User::class, $user, 'the account row stays so the ban holds');
    $this->assertTrue($user->getProfileHidden());
    $this->assertNotNull($this->em->find(Project::class, $recent_project), 'banned 13 days ago');
    $this->assertNotNull($this->em->find(Project::class, $appealing_project), 'the ban is appealed');
  }

  public function testDryRunWritesNothing(): void
  {
    $due = $this->hiddenProject('-15 days', '-15 days');
    $unstamped = $this->hiddenProject(null, '-30 days');

    $tester = new CommandTester($this->command());
    $tester->execute([]);

    $this->assertStringContainsString('Would delete project '.$due, $tester->getDisplay());
    $this->em->clear();
    $this->assertNotNull($this->em->find(Project::class, $due));
    $this->assertNull($this->em->find(Project::class, $unstamped)?->getAutoHiddenAt());
  }

  public function testHiddenWithoutTimestampStartsTheClockAtTheFirstRun(): void
  {
    $unstamped = $this->hiddenProject(null, '-30 days');

    $this->purge();

    $this->assertNotNull($this->em->find(Project::class, $unstamped)?->getAutoHiddenAt());
  }

  private function purge(): void
  {
    new CommandTester($this->command())->execute(['--apply' => true]);
    $this->em->clear();
  }

  private function command(): PurgeHiddenContentCommand
  {
    $storage = self::getContainer()->get(StorageLifecycleService::class);
    $this->assertInstanceOf(StorageLifecycleService::class, $storage);
    $doctrine = self::getContainer()->get('doctrine');
    $this->assertInstanceOf(ManagerRegistry::class, $doctrine);

    return new PurgeHiddenContentCommand($this->em, $storage, $this->studios, new NullLogger(), $doctrine);
  }

  private function user(): User
  {
    $users = self::getContainer()->get(UserDataFixtures::class);
    $this->assertInstanceOf(UserDataFixtures::class, $users);

    return $users->insertUser(['name' => 'purge'.uniqid()]);
  }

  private function project(?User $owner = null): string
  {
    $projects = self::getContainer()->get(ProjectDataFixtures::class);
    $this->assertInstanceOf(ProjectDataFixtures::class, $projects);

    return (string) $projects->insertProject(['name' => 'purge '.uniqid(), 'owned by' => ($owner ?? $this->owner)->getUsername()])->getId();
  }

  /** A project hidden $hidden ago (null: without timestamp), with a report accepted $accepted ago. */
  private function hiddenProject(?string $hidden, ?string $accepted = null): string
  {
    $id = $this->project();
    $this->em->getConnection()->executeStatement('UPDATE program SET auto_hidden = 1, auto_hidden_at = ? WHERE id = ?', [null === $hidden ? null : $this->at($hidden), $id]);
    if (null !== $accepted) {
      $this->report('project', $id, ReportState::Accepted, $accepted);
    }

    return $id;
  }

  private function comment(string $project_id, ?User $author = null): string
  {
    $project = $this->em->find(Project::class, $project_id);
    $this->assertInstanceOf(Project::class, $project);
    $author = $this->em->find(User::class, ($author ?? $this->owner)->getId());
    $this->assertInstanceOf(User::class, $author);
    $comment = new UserComment()->setProject($project)->setUser($author)->setUsername((string) $author->getUsername())->setText('text')->setUploadDate(new \DateTime());
    $this->em->persist($comment);
    $this->em->flush();

    return (string) $comment->getId();
  }

  private function report(string $type, string $id, ReportState $state, ?string $resolved = null): void
  {
    $report = new ContentReport()->setContentType($type)->setContentId($id)->setCategory('spam')->setState($state->value);
    $report->setResolvedAt(null === $resolved ? null : new \DateTime($resolved));
    $this->em->persist($report);
    $this->em->flush();
  }

  private function appeal(string $type, string $id): void
  {
    $this->em->persist(new ContentAppeal()->setContentType($type)->setContentId($id)->setReason('please'));
    $this->em->flush();
  }

  private function hold(string $type, string $id, bool $release = false): void
  {
    new CommandTester(new HoldContentCommand($this->em))->execute(['type' => $type, 'id' => $id, '--release' => $release]);
  }

  private function ban(User $user, string $since): void
  {
    $this->em->getConnection()->executeStatement('UPDATE fos_user SET profile_hidden = 1, profile_hidden_at = ? WHERE id = ?', [$this->at($since), $user->getId()]);
  }

  private function at(string $modify): string
  {
    return new \DateTime($modify)->format('Y-m-d H:i:s');
  }
}
