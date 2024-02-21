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

    $this->addSql('ALTER TABLE project_downloads ADD recommended_by_project_id INT DEFAULT NULL, ADD recommended_by_page_id INT DEFAULT NULL');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556A1748903F FOREIGN KEY (recommended_by_project_id) REFERENCES project (id)');
    $this->addSql('CREATE INDEX IDX_1D41556A1748903F ON project_downloads (recommended_by_project_id)');
    $this->addSql('ALTER TABLE click_statistics ADD scratch_project_id INT DEFAULT NULL');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE click_statistics DROP scratch_project_id');
    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY FK_1D41556A1748903F');
    $this->addSql('DROP INDEX IDX_1D41556A1748903F ON project_downloads');
    $this->addSql('ALTER TABLE project_downloads DROP recommended_by_project_id, DROP recommended_by_page_id');
  }
}
