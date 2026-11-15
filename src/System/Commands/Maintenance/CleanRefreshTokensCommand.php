<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * One bulk delete instead of gesdinet:jwt:clear, which loads every expired token into the entity manager first.
 */
#[AsCommand(
  name: 'catrobat:clean:refresh-tokens',
  description: 'Delete expired refresh tokens',
)]
class CleanRefreshTokensCommand extends Command
{
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
    $expired = $this->entity_manager->createQueryBuilder()
      ->from(RefreshToken::class, 't')
      ->where('t.valid < :now')
      ->setParameter('now', new \DateTime())
    ;

    if ($input->getOption('dry-run')) {
      $count = (int) $expired->select('COUNT(t.id)')->getQuery()->getSingleScalarResult();
      $io->success("Dry run: {$count} expired refresh tokens would be deleted.");

      return Command::SUCCESS;
    }

    $count = (int) $expired->delete()->getQuery()->execute();
    $this->cleanupLogger->info('cleanup_run', ['rule' => 'expired_refresh_tokens', 'deleted' => $count]);
    $io->success("Deleted {$count} expired refresh tokens.");

    return Command::SUCCESS;
  }
}
