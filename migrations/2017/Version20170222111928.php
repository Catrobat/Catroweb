<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170222111928 extends AbstractMigration
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

        $this->addSql('ALTER TABLE program_downloads CHANGE locale locale VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE click_statistics CHANGE locale locale VARCHAR(255) DEFAULT NULL');
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

        $this->addSql('ALTER TABLE click_statistics CHANGE locale locale VARCHAR(255) DEFAULT \'\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE program_downloads CHANGE locale locale VARCHAR(255) DEFAULT \'\' COLLATE utf8_unicode_ci');
    }
}
