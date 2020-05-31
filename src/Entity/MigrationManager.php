<?php

namespace App\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;

class MigrationManager
{
  protected EntityManagerInterface $entity_manager;

  protected Connection $connection;

  public function __construct(EntityManagerInterface $entity_manager)
  {
    $this->entity_manager = $entity_manager;
    $this->connection = $this->entity_manager->getConnection();
  }

  /**
   * @throws DBALException
   */
  public function dropMigrationVersions(): bool
  {
    $schema_manager = $this->connection->getSchemaManager();
    if (true == $schema_manager->tablesExist(['migration_versions']))
    {
      $sql = 'DROP TABLE migration_versions;';
      $connection = $this->entity_manager->getConnection();
      $stmt = $connection->prepare($sql);
      $stmt->execute();
      $stmt->closeCursor();

      return true;
    }

    return false;
  }
}
