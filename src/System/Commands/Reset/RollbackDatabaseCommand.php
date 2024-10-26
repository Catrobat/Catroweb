<?php

declare(strict_types=1);

namespace App\System\Commands\Reset;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:db:rollback', description: 'Reset test database')]
class RollbackDatabaseCommand extends Command
{
  public function __construct(protected EntityManagerInterface $entity_manager)
  {
    parent::__construct();
  }

  /**
   * @throws Exception
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $entity_manager = $this->entity_manager;
    $connection = $entity_manager->getConnection();
    $schema_manager = $connection->createSchemaManager();
    $platform = $connection->getDatabasePlatform();

    $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
    foreach ($schema_manager->listTableNames() as $tableName) {
      $q = $platform->getTruncateTableSql($tableName);
      $connection->executeStatement($q);
    }
    $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

    return 0;
  }
}
