<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:update:userranking', description: 'Recomputes the ELO ranking for all users')]
class UpdateUserRankingCommand extends Command
{
  public function __construct(protected EntityManagerInterface $entity_manager)
  {
    parent::__construct();
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output->writeln('Recomputing ELO ranking for all users');

    $userTable = $this->entity_manager->getClassMetadata(User::class)->getTableName();
    $projectTable = $this->entity_manager->getClassMetadata(Project::class)->getTableName();

    $connection = $this->entity_manager->getConnection();
    $updated = $connection->executeStatement("
      UPDATE {$userTable} u
      INNER JOIN (
        SELECT p.user_id, FLOOR(SUM(p.downloads) / COUNT(p.id)) AS score
        FROM {$projectTable} p
        WHERE p.downloads > 0
        GROUP BY p.user_id
      ) stats ON u.id = stats.user_id
      SET u.ranking_score = stats.score
    ");

    $output->writeln(sprintf('Updated %d users.', $updated));

    return 0;
  }
}
