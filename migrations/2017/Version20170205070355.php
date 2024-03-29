<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170205070355 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE program_downloads ADD recommended_by_program_id INT DEFAULT NULL, ADD recommended_by_page_id INT DEFAULT NULL');
    $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A1748903F FOREIGN KEY (recommended_by_program_id) REFERENCES program (id)');
    $this->addSql('CREATE INDEX IDX_1D41556A1748903F ON program_downloads (recommended_by_program_id)');
    $this->addSql('ALTER TABLE click_statistics ADD scratch_program_id INT DEFAULT NULL');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE click_statistics DROP scratch_program_id');
    $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556A1748903F');
    $this->addSql('DROP INDEX IDX_1D41556A1748903F ON program_downloads');
    $this->addSql('ALTER TABLE program_downloads DROP recommended_by_program_id, DROP recommended_by_page_id');
  }
}
