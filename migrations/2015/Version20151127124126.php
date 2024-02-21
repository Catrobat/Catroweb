<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151127124126 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE TABLE project_downloads (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, downloaded_at DATETIME NOT NULL, ip LONGTEXT NOT NULL, latitude LONGTEXT DEFAULT NULL, longitude LONGTEXT DEFAULT NULL, country_code LONGTEXT DEFAULT NULL, country_name LONGTEXT DEFAULT NULL, street VARCHAR(255) DEFAULT \'\', postal_code VARCHAR(255) DEFAULT \'\', locality VARCHAR(255) DEFAULT \'\', INDEX IDX_1D41556A3EB8070A (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556A3EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('DROP TABLE project_downloads');
  }
}
