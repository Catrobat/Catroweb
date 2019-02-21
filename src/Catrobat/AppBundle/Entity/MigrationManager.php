<?php

namespace Catrobat\AppBundle\Entity;


use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

/**
 * Class MigrationManager
 * @package Catrobat\AppBundle\Entity
 */
class MigrationManager
{
  /**
   * @var EntityManager
   */
  protected $entity_manager;
  /**
   * @var Connection
   */
  protected $connection;

  /**
   * MigrationManager constructor.
   *
   * @param $entity_manager
   */
  public function __construct($entity_manager)
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