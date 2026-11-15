<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\User\Notifications\CatroNotification;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'catrobat:clean:notifications',
  description: 'Delete read notifications after 90 days and unread ones after a year',
)]
class CleanNotificationsCommand extends Command
{
  /** seen => age after which a notification is deleted */
  public const array RETENTION = [1 => '-90 days', 0 => '-1 year'];

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly LoggerInterface $cleanupLogger,
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
    $dry_run = $input->getOption('dry-run');

    $count = 0;
    foreach (self::RETENTION as $seen => $age) {
      $qb = $this->entity_manager->createQueryBuilder()
        ->from(CatroNotification::class, 'n')
        ->where('n.seen = :seen')
        ->andWhere('n.created_at < :cutoff')
        ->setParameter('seen', (bool) $seen)
        ->setParameter('cutoff', new \DateTime($age))
      ;
      $count += $dry_run
        ? (int) $qb->select('COUNT(n.id)')->getQuery()->getSingleScalarResult()
        : (int) $qb->delete()->getQuery()->execute();
    }

    if ($dry_run) {
      $io->success("Dry run: {$count} notifications would be deleted.");

      return Command::SUCCESS;
    }

    $this->cleanupLogger->info('cleanup_run', ['rule' => 'old_notifications', 'deleted' => $count]);
    $io->success("Deleted {$count} old notifications.");

    return Command::SUCCESS;
  }
}
