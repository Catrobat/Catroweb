<?php

namespace App\Entity;


use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class MigrationManager
 * @package App\Entity
 */
class MigrationManager
{
  /**
   * @var EntityManagerInterface
   */
  protected $entity_manager;
  /**
   * @var Connection
   */
  protected $connection;

  /**
   * MigrationManager constructor.
   *
   * @param $entity_manager EntityManagerInterface
   */
  public function __construct(EntityManagerInterface $entity_manager)
  {
    $this->entity_manager = $entity_manager;
    $this->connection = $this->entity_manager->getConnection();
  }

  /**
   * @return bool
   * @throws \Doctrine\DBAL\DBALException
   */
  public function dropMigrationVersions()
  {
    $schema_manager = $this->connection->getSchemaManager();
    if ($schema_manager->tablesExist(['migration_versions']) == true)
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