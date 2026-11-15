<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\User\User;
use App\System\Commands\Maintenance\CleanScratchPlaceholdersCommand;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(CleanScratchPlaceholdersCommand::class)]
final class CleanScratchPlaceholdersCommandTest extends KernelTestCase
{
  public function testDeletesOnlyPlaceholdersNothingUsesAnyMore(): void
  {
    self::bootKernel();
    $container = self::getContainer();
    $em = $container->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $em);
    $users = $container->get(UserDataFixtures::class);
    $this->assertInstanceOf(UserDataFixtures::class, $users);
    $projects = $container->get(ProjectDataFixtures::class);
    $this->assertInstanceOf(ProjectDataFixtures::class, $projects);
    $user_manager = $container->get(UserManager::class);
    $this->assertInstanceOf(UserManager::class, $user_manager);

    $placeholder = static function (string $name, array $config = []) use ($users): User {
      $user = $users->insertUser(['name' => $name.uniqid()] + $config);
      $user->setScratchUserId(random_int(1, 2_000_000_000));

      return $user;
    };
    $empty = $placeholder('empty');
    $with_project = $placeholder('project');
    $followed = $placeholder('followed');
    $fresh = $placeholder('fresh');
    // A project names it as approver, so the foreign key restricts its deletion. Its id sorts first: the run goes on.
    $blocked = $placeholder('blocked', ['id' => '00000000-0000-4000-8000-'.substr(bin2hex(random_bytes(6)), 0, 12)]);
    $empty2 = $placeholder('empty2');
    $real = $users->insertUser(['name' => 'real'.uniqid()]);
    $real->addFollowing($followed);
    $followed->addFollower($real);
    $em->flush();
    $project = $projects->insertProject(['name' => 'scratch '.uniqid(), 'owned by' => $with_project->getUsername()]);
    $em->getConnection()->executeStatement('UPDATE program SET approved_by_user = ? WHERE id = ?', [$blocked->getId(), $project->getId()]);
    $em->getConnection()->executeStatement(
      'UPDATE fos_user SET updated_at = ? WHERE id IN (?, ?, ?, ?, ?, ?)',
      [new \DateTime('-2 days')->format('Y-m-d H:i:s'), $empty->getId(), $with_project->getId(), $followed->getId(), $real->getId(), $blocked->getId(), $empty2->getId()],
    );
    $ids = ['empty' => $empty->getId(), 'project' => $with_project->getId(), 'followed' => $followed->getId(), 'fresh' => $fresh->getId(), 'real' => $real->getId(), 'blocked' => $blocked->getId(), 'empty2' => $empty2->getId()];

    $doctrine = $container->get(ManagerRegistry::class);
    $this->assertInstanceOf(ManagerRegistry::class, $doctrine);
    $command = new CleanScratchPlaceholdersCommand($em, $user_manager, new NullLogger(), $doctrine);
    $dry = new CommandTester($command);
    $dry->execute(['--dry-run' => true]);
    $this->assertStringContainsString('placeholder accounts would be deleted', $dry->getDisplay());

    $run = new CommandTester($command);
    $this->assertSame(0, $run->execute([]));
    $this->assertStringContainsString('1 could not be deleted', $run->getDisplay());
    $em->clear();

    foreach ($ids as $case => $id) {
      $this->assertSame(!in_array($case, ['empty', 'empty2'], true), null !== $em->find(User::class, $id), $case);
    }
  }
}
