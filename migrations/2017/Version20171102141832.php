<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171102141832 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE CatroNotification ADD author_id INT NOT NULL');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAF675F31B FOREIGN KEY (author_id) REFERENCES fos_user (id)');
    $this->addSql('CREATE INDEX IDX_22087FCAF675F31B ON CatroNotification (author_id)');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCAF675F31B');
    $this->addSql('DROP INDEX IDX_22087FCAF675F31B ON CatroNotification');
    $this->addSql('ALTER TABLE CatroNotification DROP author_id');
  }
}
