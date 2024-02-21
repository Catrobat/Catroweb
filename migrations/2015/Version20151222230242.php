<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151222230242 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE project_downloads ADD user_id INT DEFAULT NULL, ADD referrer VARCHAR(255) DEFAULT \'\'');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('CREATE INDEX IDX_1D41556AA76ED395 ON project_downloads (user_id)');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY FK_1D41556AA76ED395');
    $this->addSql('DROP INDEX IDX_1D41556AA76ED395 ON project_downloads');
    $this->addSql('ALTER TABLE project_downloads DROP user_id, DROP referrer');
  }
}
