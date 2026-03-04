<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304090000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Make content_appeal.appellant_id nullable with ON DELETE SET NULL; add composite index on content_moderation_action';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    // Fix 7: Make appellant_id nullable with ON DELETE SET NULL
    $this->addSql('ALTER TABLE content_appeal DROP FOREIGN KEY FK_FE4F33FD851858D7');
    $this->addSql('ALTER TABLE content_appeal MODIFY appellant_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE content_appeal ADD CONSTRAINT FK_FE4F33FD851858D7 FOREIGN KEY (appellant_id) REFERENCES fos_user (id) ON DELETE SET NULL');

    // Fix 10: Add composite index for countRecentAutoHidesForUser queries
    $this->addSql('CREATE INDEX cma_action_type_created_idx ON content_moderation_action (action, content_type, created_at)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX cma_action_type_created_idx ON content_moderation_action');

    $this->addSql('ALTER TABLE content_appeal DROP FOREIGN KEY FK_FE4F33FD851858D7');
    $this->addSql('ALTER TABLE content_appeal MODIFY appellant_id CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE content_appeal ADD CONSTRAINT FK_FE4F33FD851858D7 FOREIGN KEY (appellant_id) REFERENCES fos_user (id)');
  }
}
