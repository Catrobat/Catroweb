<?php

namespace Catrobat\AppBundle\Entity;


class MigrationManager
{
  protected $entity_manager;
  protected $connection;

  public function __construct($entity_manager)
  {
    $this->entity_manager = $entity_manager;
    $this->connection = $this->entity_manager->getConnection();
  }

  public function dropMigrationVersions()
  {
    $schema_manager = $this->connection->getSchemaManager();
    if ($schema_manager->tablesExist(array('migration_versions')) == true)
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