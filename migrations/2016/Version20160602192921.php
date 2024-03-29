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

    $this->addSql('CREATE TABLE program_extension (program_id INT NOT NULL, extension_id INT NOT NULL, INDEX IDX_C985CCA83EB8070A (program_id), INDEX IDX_C985CCA8812D5EB (extension_id), PRIMARY KEY(program_id, extension_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE extension (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, prefix VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE program_extension ADD CONSTRAINT FK_C985CCA83EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_extension ADD CONSTRAINT FK_C985CCA8812D5EB FOREIGN KEY (extension_id) REFERENCES extension (id)');
  }

  public function postUp(Schema $schema): void
  {
    parent::postUp($schema);

    $this->connection->insert('extension', ['name' => 'Arduino', 'prefix' => 'ARDUINO']);
    $this->connection->insert('extension', ['name' => 'Drone', 'prefix' => 'DRONE']);
    $this->connection->insert('extension', ['name' => 'Lego', 'prefix' => 'LEGO']);
    $this->connection->insert('extension', ['name' => 'Phiro', 'prefix' => 'PHIRO']);
    $this->connection->insert('extension', ['name' => 'Raspberry Pi', 'prefix' => 'RASPI']);

    $sql = 'SELECT id FROM program WHERE lego = 1';

    $query = $this->connection->query($sql);

    while ($program = $query->fetch()) {
      $this->connection->insert('program_extension', ['program_id' => $program['id'], 'extension_id' => 3]);
    }

    $sql_2 = 'SELECT id FROM program WHERE phiro = 1';

    $query_2 = $this->connection->query($sql_2);

    while ($program = $query_2->fetch()) {
      $this->connection->insert('program_extension', ['program_id' => $program['id'], 'extension_id' => 4]);
    }
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE program_extension DROP FOREIGN KEY FK_C985CCA8812D5EB');
    $this->addSql('DROP TABLE program_extension');
    $this->addSql('DROP TABLE extension');
  }

  public function preDown(Schema $schema): void
  {
    parent::preDown($schema);

    $sql = 'SELECT program_id FROM program_extension WHERE extension_id = 3';

    $query = $this->connection->query($sql);

    while ($program = $query->fetch()) {
      $this->connection->update('program', ['lego' => 1], ['id' => $program['program_id']]);
    }

    $sql_2 = 'SELECT program_id FROM program_extension WHERE extension_id = 4';

    $query_2 = $this->connection->query($sql_2);

    while ($program = $query_2->fetch()) {
      $this->connection->update('program', ['phiro' => 1], ['id' => $program['program_id']]);
    }
  }
}
