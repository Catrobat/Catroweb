<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Only the comment table goes: the previous release writes it from a kernel.terminate listener (after the response
 * is sent), never on a user request. project_machine_translation keeps its usage columns for now, because the
 * previous release selects them on every project translation and project edit (deploy window, rollback).
 */
final class Version20261117120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Drop the comment machine translation usage statistics';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('DROP TABLE IF EXISTS user_comment_machine_translation');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('CREATE TABLE user_comment_machine_translation (id INT AUTO_INCREMENT NOT NULL, comment_id CHAR(36) DEFAULT NULL, source_language VARCHAR(5) NOT NULL, target_language VARCHAR(5) NOT NULL, provider VARCHAR(255) NOT NULL, usage_count INT NOT NULL, usage_per_month DOUBLE PRECISION NOT NULL, last_modified_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_2CEF8196F8697D13 (comment_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE user_comment_machine_translation ADD CONSTRAINT FK_2CEF8196F8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id) ON DELETE CASCADE');
  }
}
