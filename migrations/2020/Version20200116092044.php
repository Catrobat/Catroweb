<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200116092044 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY IF EXISTS FK_CC794C66F1496545');
    $this->addSql('DROP INDEX IF EXISTS IDX_CC794C66F1496545 ON user_comment');
    $this->addSql('ALTER TABLE user_comment DROP IF EXISTS projects');
    $this->addSql('ALTER TABLE user_comment DROP COLUMN IF EXISTS projectId');
    $this->addSql('ALTER TABLE user_comment ADD COLUMN IF NOT EXISTS projectId CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66BB3368CF FOREIGN KEY (projectId) REFERENCES project (id)');
    $this->addSql('CREATE INDEX IDX_CC794C66BB3368CF ON user_comment (projectId)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY IF EXISTS FK_CC794C66BB3368CF');
    $this->addSql('DROP INDEX IF EXISTS IDX_CC794C66BB3368CF ON user_comment');
    $this->addSql('ALTER TABLE user_comment ADD projects CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', CHANGE projectId projectId INT NOT NULL');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66F1496545 FOREIGN KEY (projects) REFERENCES project (id)');
    $this->addSql('CREATE INDEX IDX_CC794C66F1496545 ON user_comment (projects)');
  }
}
