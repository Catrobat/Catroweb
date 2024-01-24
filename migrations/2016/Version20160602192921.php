<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160602192921 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE TABLE project_extension (project_id INT NOT NULL, extension_id INT NOT NULL, INDEX IDX_C985CCA83EB8070A (project_id), INDEX IDX_C985CCA8812D5EB (extension_id), PRIMARY KEY(project_id, extension_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE extension (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, prefix VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE project_extension ADD CONSTRAINT FK_C985CCA83EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_extension ADD CONSTRAINT FK_C985CCA8812D5EB FOREIGN KEY (extension_id) REFERENCES extension (id)');
  }

  public function postUp(Schema $schema): void
  {
    parent::postUp($schema);

    $this->connection->insert('extension', ['name' => 'Arduino', 'prefix' => 'ARDUINO']);
    $this->connection->insert('extension', ['name' => 'Drone', 'prefix' => 'DRONE']);
    $this->connection->insert('extension', ['name' => 'Lego', 'prefix' => 'LEGO']);
    $this->connection->insert('extension', ['name' => 'Phiro', 'prefix' => 'PHIRO']);
    $this->connection->insert('extension', ['name' => 'Raspberry Pi', 'prefix' => 'RASPI']);

    $sql = 'SELECT id FROM project WHERE lego = 1';

    $query = $this->connection->query($sql);

    while ($project = $query->fetch()) {
      $this->connection->insert('project_extension', ['project_id' => $project['id'], 'extension_id' => 3]);
    }

    $sql_2 = 'SELECT id FROM project WHERE phiro = 1';

    $query_2 = $this->connection->query($sql_2);

    while ($project = $query_2->fetch()) {
      $this->connection->insert('project_extension', ['project_id' => $project['id'], 'extension_id' => 4]);
    }
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE project_extension DROP FOREIGN KEY FK_C985CCA8812D5EB');
    $this->addSql('DROP TABLE project_extension');
    $this->addSql('DROP TABLE extension');
  }

  public function preDown(Schema $schema): void
  {
    parent::preDown($schema);

    $sql = 'SELECT project_id FROM project_extension WHERE extension_id = 3';

    $query = $this->connection->query($sql);

    while ($project = $query->fetch()) {
      $this->connection->update('project', ['lego' => 1], ['id' => $project['project_id']]);
    }

    $sql_2 = 'SELECT project_id FROM project_extension WHERE extension_id = 4';

    $query_2 = $this->connection->query($sql_2);

    while ($project = $query_2->fetch()) {
      $this->connection->update('project', ['phiro' => 1], ['id' => $project['project_id']]);
    }
  }
}
