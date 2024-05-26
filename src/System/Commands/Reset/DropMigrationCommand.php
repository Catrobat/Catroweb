<?php

declare(strict_types=1);

namespace App\System\Commands\Reset;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:drop:migration', description: 'Dropping the doctrine_migration_versions table')]
class DropMigrationCommand extends Command
{
  protected Connection $connection;

  private OutputInterface $output;

  public function __construct(protected EntityManagerInterface $entity_manager)
  {
    parent::__construct();
    $this->connection = $this->entity_manager->getConnection();
  }

  /**
   * @throws Exception
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->output = $output;
    if ($this->dropMigrationVersions()) {
      $this->output->writeln('Table doctrine_migration_versions dropped!');
    } else {
      $this->output->writeln("Table doctrine_migration_versions doesn't exist!");
    }

    return 0;
  }

  /**
   * @throws Exception
   */
  private function dropMigrationVersions(): bool
  {
    $schema_manager = $this->connection->createSchemaManager();
    if ($schema_manager->tablesExist(['doctrine_migration_versions'])) {
      $sql = 'DROP TABLE doctrine_migration_versions;';
      $connection = $this->entity_manager->getConnection();
      $stmt = $connection->prepare($sql);
      $stmt->executeStatement();

      return true;
    }

    return false;
  }
}
