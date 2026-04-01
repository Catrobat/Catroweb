<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\User\User;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'catrobat:clean:unverified-users',
  description: 'Delete user accounts that have not been verified within the retention period',
)]
class CleanUnverifiedUsersCommand extends Command
{
  private const int DEFAULT_RETENTION_DAYS = 30;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly UserManager $user_manager,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addOption(
      'days',
      'd',
      InputOption::VALUE_REQUIRED,
      'Number of days after which unverified accounts are deleted',
      (string) self::DEFAULT_RETENTION_DAYS,
    );
    $this->addOption(
      'dry-run',
      null,
      InputOption::VALUE_NONE,
      'Show which users would be deleted without actually deleting them',
    );
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $days = (int) $input->getOption('days');
    $dryRun = $input->getOption('dry-run');

    $cutoff = new \DateTimeImmutable("-{$days} days");

    $qb = $this->entity_manager->createQueryBuilder();
    $users = $qb->select('u')
      ->from(User::class, 'u')
      ->where('u.verified = :verified')
      ->andWhere('u.createdAt < :cutoff')
      ->setParameter('verified', false)
      ->setParameter('cutoff', $cutoff)
      ->getQuery()
      ->getResult()
    ;

    $count = count($users);

    if (0 === $count) {
      $io->success('No unverified accounts older than '.$days.' days found.');

      return Command::SUCCESS;
    }

    if ($dryRun) {
      $io->note("Dry run: would delete {$count} unverified account(s):");
      foreach ($users as $user) {
        $io->writeln(sprintf('  - %s (%s), created %s', $user->getUsername(), $user->getEmail(), $user->getCreatedAt()?->format('Y-m-d') ?? 'unknown'));
      }

      return Command::SUCCESS;
    }

    foreach ($users as $user) {
      $io->writeln(sprintf('Deleting: %s (%s)', $user->getUsername(), $user->getEmail()));
      $this->user_manager->delete($user);
    }

    $io->success("Deleted {$count} unverified account(s) older than {$days} days.");

    return Command::SUCCESS;
  }
}
