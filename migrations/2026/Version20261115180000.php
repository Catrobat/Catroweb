<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rows already hidden get no timestamp here: catro:moderation:purge-hidden stamps them on its first --apply run,
 * so nothing hidden before the deploy is deleted earlier than 14 days after it.
 */
final class Version20261115180000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Since when content is hidden and an account is banned, and which audit rows are hide and ban decisions, for the moderation purge';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE program ADD COLUMN IF NOT EXISTS auto_hidden_at DATETIME DEFAULT NULL');
    $this->addSql('ALTER TABLE studio ADD COLUMN IF NOT EXISTS auto_hidden_at DATETIME DEFAULT NULL');
    $this->addSql('ALTER TABLE user_comment ADD COLUMN IF NOT EXISTS auto_hidden_at DATETIME DEFAULT NULL');
    $this->addSql('ALTER TABLE fos_user ADD COLUMN IF NOT EXISTS profile_hidden_at DATETIME DEFAULT NULL');

    // hide_warn was audited like a plain warn; only a hide counts for the purge.
    $this->addSql("UPDATE content_moderation_action SET action = 'admin_hide_warned' WHERE action = 'admin_warned' AND content_type <> 'user' AND note LIKE '%resolved via admin: hide_warn'");
    // Owners banned by an accepted report on their project or comment get the admin_banned row accepts write now.
    // Stamped now, so their content is not deleted before 14 days after the deploy.
    foreach (['project' => 'program', 'comment' => 'user_comment'] as $type => $table) {
      $this->addSql("INSERT INTO content_moderation_action (content_type, content_id, action, note, created_at)
        SELECT DISTINCT 'user', x.user_id, 'admin_banned', 'Backfill: owner of an accepted {$type}', NOW()
        FROM content_moderation_action a JOIN {$table} x ON x.id = a.content_id JOIN fos_user u ON u.id = x.user_id
        WHERE a.content_type = '{$type}' AND a.action = 'admin_confirmed' AND u.profile_hidden = 1");
    }
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql("DELETE FROM content_moderation_action WHERE action = 'admin_banned' AND note LIKE 'Backfill: owner of an accepted %'");
    $this->addSql("UPDATE content_moderation_action SET action = 'admin_warned' WHERE action = 'admin_hide_warned'");
    $this->addSql('ALTER TABLE program DROP COLUMN auto_hidden_at');
    $this->addSql('ALTER TABLE studio DROP COLUMN auto_hidden_at');
    $this->addSql('ALTER TABLE user_comment DROP COLUMN auto_hidden_at');
    $this->addSql('ALTER TABLE fos_user DROP COLUMN profile_hidden_at');
  }
}
