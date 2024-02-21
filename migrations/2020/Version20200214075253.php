<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200214075253 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE CatroNotification ADD remix_from CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD remix_project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA976EECBE FOREIGN KEY (remix_from) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA63B7B817 FOREIGN KEY (remix_project_id) REFERENCES project (id)');
    $this->addSql('CREATE INDEX IDX_22087FCA976EECBE ON CatroNotification (remix_from)');
    $this->addSql('CREATE INDEX IDX_22087FCA63B7B817 ON CatroNotification (remix_project_id)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA976EECBE');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA63B7B817');
    $this->addSql('DROP INDEX IDX_22087FCA976EECBE ON CatroNotification');
    $this->addSql('DROP INDEX IDX_22087FCA63B7B817 ON CatroNotification');
    $this->addSql('ALTER TABLE CatroNotification DROP remix_from, DROP remix_project_id');
  }
}
