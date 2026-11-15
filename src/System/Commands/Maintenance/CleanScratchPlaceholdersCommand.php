<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A Scratch import creates a placeholder account for the project's Scratch author. Once none of its projects is
 * left, nobody can log in to it and nothing shows it: delete it. A later import of that author creates a new one.
 */
#[AsCommand(
  name: 'catrobat:clean:scratch-placeholders',
  description: 'Delete Scratch author placeholder accounts that have no projects left',
)]
class CleanScratchPlaceholdersCommand extends Command
{
  /** An import creates the placeholder before its project: leave the import a day to finish. */
  public const string GRACE_PERIOD = '-1 day';

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly UserManager $user_manager,
    private readonly LoggerInterface $cleanupLogger,
    private readonly ManagerRegistry $doctrine,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only count what would be deleted');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);

    if ($input->getOption('dry-run')) {
      $count = (int) $this->placeholders()->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();
      $io->success("Dry run: {$count} Scratch placeholder accounts would be deleted.");

      return Command::SUCCESS;
    }

    /** @var list<string> $ids */
    $ids = array_map(strval(...), $this->placeholders()->select('u.id')->orderBy('u.id')->getQuery()->getSingleColumnResult());
    $deleted = 0;
    $failed = 0;
    foreach ($ids as $id) {
      try {
        $user = $this->entity_manager->find(User::class, $id);
        if ($user instanceof User) {
          $this->user_manager->delete($user);
          ++$deleted;
        }
      } catch (\Throwable $e) {
        // A row that still points at the account (a restricting foreign key) must not stop the others.
        ++$failed;
        $this->cleanupLogger->warning('cleanup_skipped', ['rule' => 'scratch_placeholders', 'user_id' => $id, 'error' => $e->getMessage()]);
        $this->doctrine->resetManager(); // a failed flush closes the entity manager
      }

      $this->entity_manager->clear();
    }

    $this->cleanupLogger->info('cleanup_run', ['rule' => 'scratch_placeholders', 'deleted' => $deleted, 'failed' => $failed]);
    $io->success("Deleted {$deleted} Scratch placeholder accounts, {$failed} could not be deleted.");

    return Command::SUCCESS;
  }

  /**
   * Placeholders without projects. One a real user follows stays: the follow would vanish with it.
   */
  private function placeholders(): QueryBuilder
  {
    return $this->entity_manager->createQueryBuilder()
      ->select('u')
      ->from(User::class, 'u')
      ->where('u.scratch_user_id IS NOT NULL')
      ->andWhere('u.lastLogin IS NULL')
      ->andWhere('u.updatedAt < :grace')
      ->andWhere('u.followers IS EMPTY')
      ->andWhere('NOT EXISTS (SELECT p.id FROM '.Project::class.' p WHERE p.user = u)')
      ->setParameter('grace', new \DateTime(self::GRACE_PERIOD))
    ;
  }
}
