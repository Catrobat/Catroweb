<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303080247 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add trust-weighted community moderation system: content_report, content_appeal, content_moderation_action tables, auto_hidden/profile_hidden columns, moderation notification columns, and remove legacy report infrastructure';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE IF NOT EXISTS content_appeal (id INT AUTO_INCREMENT NOT NULL, content_type VARCHAR(20) NOT NULL, content_id VARCHAR(255) NOT NULL, reason LONGTEXT NOT NULL, state SMALLINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, resolved_at DATETIME DEFAULT NULL, resolution_note LONGTEXT DEFAULT NULL, appellant_id CHAR(36) NOT NULL, resolved_by_id CHAR(36) DEFAULT NULL, INDEX IDX_FE4F33FD851858D7 (appellant_id), INDEX IDX_FE4F33FD6713A32B (resolved_by_id), INDEX ca_state_created_idx (state, created_at), UNIQUE INDEX unique_pending_appeal (content_type, content_id, appellant_id, state), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('CREATE TABLE IF NOT EXISTS content_moderation_action (id INT AUTO_INCREMENT NOT NULL, content_type VARCHAR(20) NOT NULL, content_id VARCHAR(255) NOT NULL, action VARCHAR(30) NOT NULL, cumulative_score DOUBLE PRECISION DEFAULT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, performed_by_id CHAR(36) DEFAULT NULL, INDEX IDX_E3562E692E65C292 (performed_by_id), INDEX cma_content_idx (content_type, content_id), INDEX cma_created_idx (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('CREATE TABLE IF NOT EXISTS content_report (id INT AUTO_INCREMENT NOT NULL, content_type VARCHAR(20) NOT NULL, content_id VARCHAR(255) NOT NULL, category VARCHAR(100) NOT NULL, note LONGTEXT DEFAULT NULL, state SMALLINT DEFAULT 1 NOT NULL, reporter_trust_score DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, resolved_at DATETIME DEFAULT NULL, reporter_id CHAR(36) DEFAULT NULL, resolved_by_id CHAR(36) DEFAULT NULL, INDEX IDX_AC190728E1CFE6F5 (reporter_id), INDEX IDX_AC1907286713A32B (resolved_by_id), INDEX cr_content_idx (content_type, content_id, state), INDEX cr_reporter_idx (reporter_id, state), INDEX cr_state_created_idx (state, created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('ALTER TABLE content_appeal ADD CONSTRAINT FK_FE4F33FD851858D7 FOREIGN KEY (appellant_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE content_appeal ADD CONSTRAINT FK_FE4F33FD6713A32B FOREIGN KEY (resolved_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE content_moderation_action ADD CONSTRAINT FK_E3562E692E65C292 FOREIGN KEY (performed_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE content_report ADD CONSTRAINT FK_AC190728E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE content_report ADD CONSTRAINT FK_AC1907286713A32B FOREIGN KEY (resolved_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE CatroNotification ADD moderation_content_type VARCHAR(20) DEFAULT NULL, ADD moderation_content_id VARCHAR(255) DEFAULT NULL, ADD moderation_action VARCHAR(30) DEFAULT NULL');
    $this->addSql('ALTER TABLE fos_user ADD profile_hidden TINYINT DEFAULT 0 NOT NULL');
    $this->addSql('ALTER TABLE program ADD auto_hidden TINYINT DEFAULT 0 NOT NULL');
    $this->addSql('ALTER TABLE studio ADD auto_hidden TINYINT DEFAULT 0 NOT NULL');
    $this->addSql('ALTER TABLE user_comment ADD auto_hidden TINYINT DEFAULT 0 NOT NULL');
    $this->addSql('ALTER TABLE user_comment DROP isReported');
    $this->addSql('DROP TABLE IF EXISTS program_inappropriate_report');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE content_appeal DROP FOREIGN KEY FK_FE4F33FD851858D7');
    $this->addSql('ALTER TABLE content_appeal DROP FOREIGN KEY FK_FE4F33FD6713A32B');
    $this->addSql('ALTER TABLE content_moderation_action DROP FOREIGN KEY FK_E3562E692E65C292');
    $this->addSql('ALTER TABLE content_report DROP FOREIGN KEY FK_AC190728E1CFE6F5');
    $this->addSql('ALTER TABLE content_report DROP FOREIGN KEY FK_AC1907286713A32B');
    $this->addSql('DROP TABLE content_appeal');
    $this->addSql('DROP TABLE content_moderation_action');
    $this->addSql('DROP TABLE content_report');
    $this->addSql('ALTER TABLE CatroNotification DROP moderation_content_type, DROP moderation_content_id, DROP moderation_action');
    $this->addSql('ALTER TABLE fos_user DROP profile_hidden');
    $this->addSql('ALTER TABLE program DROP auto_hidden');
    $this->addSql('ALTER TABLE studio DROP auto_hidden');
    $this->addSql('ALTER TABLE user_comment DROP auto_hidden');
    $this->addSql('ALTER TABLE user_comment ADD isReported TINYINT(1) NOT NULL DEFAULT 0');
  }
}
