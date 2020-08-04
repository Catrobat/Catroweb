<?php

namespace App\Commands\Reset;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DropMigrationCommand extends Command
{
  protected static $defaultName = 'catrobat:drop:migration';

  protected EntityManagerInterface $entity_manager;

  protected Connection $connection;
  private OutputInterface $output;

  public function __construct(EntityManagerInterface $entity_manager)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
    $this->connection = $this->entity_manager->getConnection();
  }

  protected function configure(): void
  {
    $this->setName('catrobat:drop:migration')
      ->setDescription('Dropping the migration_versions table')
    ;
  }

  /**
   * @throws DBALException
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->output = $output;
    if ($this->dropMigrationVersions())
    {
      $this->output->writeln('Table migration_versions dropped!');
    }
    else
    {
      $this->output->writeln('Table migration_versions doesn\'t exist!');
    }

    return 0;
  }

  /**
   * @throws DBALException
   */
  private function dropMigrationVersions(): bool
  {
    $schema_manager = $this->connection->getSchemaManager();
    if ($schema_manager->tablesExist(['migration_versions']))
    {
      $sql = 'DROP TABLE migration_versions;';
      $connection = $this->entity_manager->getConnection();
      $stmt = $connection->prepare($sql);
      $stmt->execute();
      $stmt->closeCursor();

      return true;
    }

    if ($schema_manager->tablesExist(['doctrine_migration_versions']))
    {
      $sql = 'DROP TABLE doctrine_migration_versions;';
      $connection = $this->entity_manager->getConnection();
      $stmt = $connection->prepare($sql);
      $stmt->execute();
      $stmt->closeCursor();

      return true;
    }

    return false;
  }
}
