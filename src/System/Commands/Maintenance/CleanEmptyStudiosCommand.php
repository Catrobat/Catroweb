<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Notifications\StudioDeletionWarningNotification;
use App\Studio\StudioManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A studio without projects in which nothing happened for 90 days: its admins (or, without one, its members) get an
 * in-app warning, and 30 days later it is deleted unless a project was added or anything else happened in it.
 * Featured studios, studios with a pending join request and studios with no one to warn stay.
 */
#[AsCommand(
  name: 'catrobat:clean:empty-studios',
  description: 'Warn about and later delete studios that have no projects and no activity',
)]
class CleanEmptyStudiosCommand extends Command
{
  public const string INACTIVE_FOR = '-90 days';

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly StudioManager $studio_manager,
    private readonly LoggerInterface $cleanupLogger,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only count what would be warned, deleted or kept');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $deletion_due = new \DateTime(sprintf('-%d days', StudioDeletionWarningNotification::DAYS_UNTIL_DELETION))->format('Y-m-d H:i:s');

    // A warned studio is judged by what happened since its warning; an unwarned one by the last 90 days.
    $kept = $this->studioIds('s.deletion_warned_at IS NOT NULL AND NOT '.self::idleSince('s.deletion_warned_at'));
    $deleted = $this->studioIds('s.deletion_warned_at < :due AND '.self::idleSince('s.deletion_warned_at'), ['due' => $deletion_due]);
    $warned = $this->studioIds('s.deletion_warned_at IS NULL AND '.self::idleSince(':cutoff'), ['cutoff' => new \DateTime(self::INACTIVE_FOR)->format('Y-m-d H:i:s')]);

    if ($input->getOption('dry-run')) {
      $io->success(sprintf('Dry run: %d studios would be warned, %d deleted, %d kept because something happened.', count($warned), count($deleted), count($kept)));

      return Command::SUCCESS;
    }

    foreach ($this->studios($kept) as $studio) {
      $studio->setDeletionWarnedAt(null);
    }

    foreach ($this->studios($deleted) as $studio) {
      $this->studio_manager->deleteCoverImage($studio->getCoverAssetPath());
      $this->entity_manager->remove($studio);
    }

    $now = new \DateTime();
    $unwarnable = 0;
    foreach ($this->studios($warned) as $studio) {
      $recipients = $this->recipients($studio);
      if ([] === $recipients) {
        ++$unwarnable; // no one to tell: never warned, so never deleted
        continue;
      }

      $studio->setDeletionWarnedAt($now);
      foreach ($recipients as $member) {
        $this->entity_manager->persist(new StudioDeletionWarningNotification($member->getUser(), $studio));
      }
    }

    $this->entity_manager->flush();

    $warned_count = count($warned) - $unwarnable;
    $this->cleanupLogger->info('cleanup_run', ['rule' => 'empty_studios', 'deleted' => count($deleted), 'warned' => $warned_count, 'kept' => count($kept), 'no_one_to_warn' => $unwarnable]);
    $io->success(sprintf('Warned %d studios, deleted %d, kept %d because something happened, %d have no one to warn.', $warned_count, count($deleted), count($kept), $unwarnable));

    return Command::SUCCESS;
  }

  /**
   * SQL condition on studio s: no projects, not featured, no pending join request, and nothing happened in it since
   * $since (an SQL expression).
   */
  private static function idleSince(string $since): string
  {
    return "(NOT EXISTS (SELECT 1 FROM studio_program sp WHERE sp.studio = s.id)
      AND NOT EXISTS (SELECT 1 FROM featured_studio f WHERE f.studio_id = s.id)
      AND s.created_on < {$since} AND COALESCE(s.updated_on, s.created_on) < {$since}
      AND NOT EXISTS (SELECT 1 FROM studio_activity a WHERE a.studio = s.id AND a.created_on >= {$since})
      AND NOT EXISTS (SELECT 1 FROM studio_join_requests j WHERE j.studio = s.id AND j.status = 'pending'))";
  }

  /**
   * @param array<string, string> $params
   *
   * @return list<string>
   */
  private function studioIds(string $where, array $params = []): array
  {
    return array_map(strval(...), $this->entity_manager->getConnection()->fetchFirstColumn('SELECT s.id FROM studio s WHERE '.$where, $params));
  }

  /**
   * The active admins; a studio without one warns all its active members.
   *
   * @return list<StudioUser>
   */
  private function recipients(Studio $studio): array
  {
    $repository = $this->entity_manager->getRepository(StudioUser::class);
    $admins = $repository->findBy(['studio' => $studio, 'role' => StudioUser::ROLE_ADMIN, 'status' => StudioUser::STATUS_ACTIVE]);

    return [] !== $admins ? $admins : $repository->findBy(['studio' => $studio, 'status' => StudioUser::STATUS_ACTIVE]);
  }

  /**
   * @param list<string> $ids
   *
   * @return list<Studio>
   */
  private function studios(array $ids): array
  {
    return [] === $ids ? [] : $this->entity_manager->getRepository(Studio::class)->findBy(['id' => $ids]);
  }
}
