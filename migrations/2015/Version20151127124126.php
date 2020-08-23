<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151127124126 extends AbstractMigration
{
  /**
   * @param Schema $schema
   *
   * @throws \Doctrine\DBAL\DBALException
   */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE program_downloads (id INT AUTO_INCREMENT NOT NULL, program_id INT NOT NULL, downloaded_at DATETIME NOT NULL, ip LONGTEXT NOT NULL, latitude LONGTEXT DEFAULT NULL, longitude LONGTEXT DEFAULT NULL, country_code LONGTEXT DEFAULT NULL, country_name LONGTEXT DEFAULT NULL, street VARCHAR(255) DEFAULT \'\', postal_code VARCHAR(255) DEFAULT \'\', locality VARCHAR(255) DEFAULT \'\', INDEX IDX_1D41556A3EB8070A (program_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    }

  /**
   * @param Schema $schema
   *
   * @throws \Doctrine\DBAL\DBALException
   */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE program_downloads');
    }
}
