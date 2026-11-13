<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\Notifications\ProjectFilesLostNotification;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * One-off: tells every owner of projects flagged has_missing_files (by catrobat:workflow:detect_broken_projects,
 * which must have run first) to re-upload them. Re-running only announces projects that no notice since their last
 * upload lists. StorageLifecycleService deletes projects still without files
 * LOST_FILES_GRACE_DAYS after their notice.
 */
#[AsCommand(
  name: 'catrobat:projects:notify-lost-files',
  description: 'Send one in-app notice per owner whose projects lost their files, asking to re-upload them',
)]
class NotifyLostProjectFilesCommand extends Command
{
  /** Keeps the JSON in the notice's TEXT column well below its 64 KB limit. */
  private const int MAX_PROJECTS_PER_NOTICE = 200;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'List the notices without creating them');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $dry_run = true === $input->getOption('dry-run');

    $latest_notice = ProjectFilesLostNotification::latestNoticePerProject(
      $this->entity_manager->getRepository(ProjectFilesLostNotification::class)->findAll()
    );

    /** @var list<array{id: string, name: string, uploaded_at: \DateTimeInterface, user_id: string}> $rows */
    $rows = $this->entity_manager->createQueryBuilder()
      ->select('p.id', 'p.name', 'p.uploaded_at', 'IDENTITY(p.user) AS user_id')
      ->from(Project::class, 'p')
      ->where('p.has_missing_files = true')
      ->andWhere('p.user IS NOT NULL')
      ->orderBy('p.uploaded_at', 'DESC')
      ->getQuery()
      ->getArrayResult()
    ;

    /** @var array<string, array<string, string>> $projects_by_owner */
    $projects_by_owner = [];
    foreach ($rows as $row) {
      $id = (string) $row['id'];
      // A notice from before the last upload was about an earlier loss: announce this one again.
      if (!isset($latest_notice[$id]) || $latest_notice[$id] < $row['uploaded_at']) {
        $projects_by_owner[(string) $row['user_id']][$id] = (string) $row['name'];
      }
    }

    foreach ($projects_by_owner as $user_id => $projects) {
      $io->writeln(sprintf('%s: %d project(s)', $user_id, count($projects)));
      $user = $dry_run ? null : $this->entity_manager->find(User::class, $user_id);
      if (!$user instanceof User) {
        continue;
      }

      foreach (array_chunk($projects, self::MAX_PROJECTS_PER_NOTICE, true) as $chunk) {
        $this->entity_manager->persist(new ProjectFilesLostNotification($user, $chunk));
      }
      // Per owner: a failure keeps the notices already sent, and a re-run sends the rest.
      $this->entity_manager->flush();
    }

    $io->success(sprintf(
      '%s %d owner(s) about %d project(s).',
      $dry_run ? 'Would notify' : 'Notified',
      count($projects_by_owner),
      array_sum(array_map(count(...), $projects_by_owner)),
    ));

    return Command::SUCCESS;
  }
}
