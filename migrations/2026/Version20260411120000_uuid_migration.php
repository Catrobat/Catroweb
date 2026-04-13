<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migrate all API-exposed entity PKs from INT AUTO_INCREMENT to UUID (CHAR(36)).
 *
 * Strategy: For each table with incoming FKs, add a temp uuid column, populate it,
 * propagate to FK columns via JOIN, then swap the PK. Tables without incoming FKs
 * just get a direct column type change + backfill.
 */
final class Version20260411120000_uuid_migration extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Migrate entity PKs from INT to UUID for all API-exposed entities.';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    // ──────────────────────────────────────────────
    // 1. studio_activity (referenced by studio_user.activity, studio_program.activity, user_comment.activity)
    // ──────────────────────────────────────────────

    // Drop FKs pointing to studio_activity.id
    $this->dropForeignKeyByReference('studio_user', 'activity', 'studio_activity');
    $this->dropForeignKeyByReference('studio_program', 'activity', 'studio_activity');
    $this->dropForeignKeyByReference('user_comment', 'activity', 'studio_activity');

    // Add temp uuid column, populate, propagate to FK columns via JOIN
    $this->addSql('ALTER TABLE studio_activity ADD uuid_new CHAR(36) DEFAULT NULL');
    $this->addSql('UPDATE studio_activity SET uuid_new = UUID()');

    $this->addSql('UPDATE studio_user su JOIN studio_activity sa ON su.activity = sa.id SET su.activity = sa.uuid_new');
    $this->addSql('UPDATE studio_program sp JOIN studio_activity sa ON sp.activity = sa.id SET sp.activity = sa.uuid_new');
    $this->addSql('UPDATE user_comment uc JOIN studio_activity sa ON uc.activity = sa.id SET uc.activity = sa.uuid_new');

    // Swap PK to new UUID
    $this->addSql('UPDATE studio_activity SET id = uuid_new');
    $this->addSql('ALTER TABLE studio_activity DROP COLUMN uuid_new');
    $this->addSql('ALTER TABLE studio_activity MODIFY id CHAR(36) NOT NULL');

    // Change FK column types
    $this->addSql('ALTER TABLE studio_user MODIFY activity CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE studio_program MODIFY activity CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE user_comment MODIFY activity CHAR(36) DEFAULT NULL');

    // Re-add FKs
    $this->addSql('ALTER TABLE studio_user ADD CONSTRAINT FK_EC686DD1AC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_program ADD CONSTRAINT FK_4CB3C24AAC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66AC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');

    // ──────────────────────────────────────────────
    // 2. user_comment (referenced by CatroNotification.comment_id,
    //    user_comment_machine_translation.comment_id, self-ref parent_id)
    // ──────────────────────────────────────────────

    // Drop FKs pointing to user_comment.id
    $this->dropForeignKeyByReference('CatroNotification', 'comment_id', 'user_comment');
    $this->dropForeignKeyByReference('user_comment_machine_translation', 'comment_id', 'user_comment');

    // Clean up parent_id: set 0 values to NULL (legacy "no parent" convention)
    $this->addSql("UPDATE user_comment SET parent_id = NULL WHERE parent_id = '0' OR parent_id = ''");

    // Add temp uuid column, populate, propagate
    $this->addSql('ALTER TABLE user_comment ADD uuid_new CHAR(36) DEFAULT NULL');
    $this->addSql('UPDATE user_comment SET uuid_new = UUID()');

    // Propagate to self-referencing parent_id
    $this->addSql('UPDATE user_comment child JOIN user_comment parent ON child.parent_id = parent.id SET child.parent_id = parent.uuid_new WHERE child.parent_id IS NOT NULL');
    // Propagate to CatroNotification.comment_id
    $this->addSql('UPDATE CatroNotification cn JOIN user_comment uc ON cn.comment_id = uc.id SET cn.comment_id = uc.uuid_new WHERE cn.comment_id IS NOT NULL');
    // Propagate to user_comment_machine_translation.comment_id
    $this->addSql('UPDATE user_comment_machine_translation ucmt JOIN user_comment uc ON ucmt.comment_id = uc.id SET ucmt.comment_id = uc.uuid_new WHERE ucmt.comment_id IS NOT NULL');

    // Swap PK
    $this->addSql('UPDATE user_comment SET id = uuid_new');
    $this->addSql('ALTER TABLE user_comment DROP COLUMN uuid_new');
    $this->addSql('ALTER TABLE user_comment MODIFY id CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE user_comment MODIFY parent_id CHAR(36) DEFAULT NULL');

    // Change FK column types
    $this->addSql('ALTER TABLE CatroNotification MODIFY comment_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE user_comment_machine_translation MODIFY comment_id CHAR(36) DEFAULT NULL');

    // Re-add FKs
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAF8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE user_comment_machine_translation ADD CONSTRAINT FK_2CEF8196F8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id) ON DELETE CASCADE');

    // ──────────────────────────────────────────────
    // 3. CatroNotification (no incoming FKs to its PK)
    // ──────────────────────────────────────────────

    $this->addSql('ALTER TABLE CatroNotification MODIFY id CHAR(36) NOT NULL');
    $this->addSql('UPDATE CatroNotification SET id = UUID() WHERE CHAR_LENGTH(id) < 36');

    // ──────────────────────────────────────────────
    // 4. achievement (referenced by user_achievement.achievement)
    // ──────────────────────────────────────────────

    $this->dropForeignKeyByReference('user_achievement', 'achievement', 'achievement');

    $this->addSql('ALTER TABLE achievement ADD uuid_new CHAR(36) DEFAULT NULL');
    $this->addSql('UPDATE achievement SET uuid_new = UUID()');
    $this->addSql('UPDATE user_achievement ua JOIN achievement a ON ua.achievement = a.id SET ua.achievement = a.uuid_new');
    $this->addSql('UPDATE achievement SET id = uuid_new');
    $this->addSql('ALTER TABLE achievement DROP COLUMN uuid_new');
    $this->addSql('ALTER TABLE achievement MODIFY id CHAR(36) NOT NULL');

    $this->addSql('ALTER TABLE user_achievement MODIFY achievement CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE user_achievement ADD CONSTRAINT FK_3F68B66496737FF1 FOREIGN KEY (achievement) REFERENCES achievement (id) ON DELETE CASCADE');

    // ──────────────────────────────────────────────
    // 5. user_achievement (no incoming FKs to its PK)
    // ──────────────────────────────────────────────

    $this->addSql('ALTER TABLE user_achievement MODIFY id CHAR(36) NOT NULL');
    $this->addSql('UPDATE user_achievement SET id = UUID() WHERE CHAR_LENGTH(id) < 36');

    // ──────────────────────────────────────────────
    // 6-10. Simple tables (no incoming FKs to their PKs)
    // ──────────────────────────────────────────────

    foreach (['content_report', 'content_appeal', 'studio_user', 'studio_join_requests', 'featured_banner'] as $table) {
      $this->addSql("ALTER TABLE {$table} MODIFY id CHAR(36) NOT NULL");
      $this->addSql("UPDATE {$table} SET id = UUID() WHERE CHAR_LENGTH(id) < 36");
    }
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->throwIrreversibleMigrationException('UUID migration cannot be reversed — data would be lost.');
  }

  private function dropForeignKeyByReference(string $table, string $column, string $referenced_table): void
  {
    $schema = $this->connection->fetchOne('SELECT DATABASE()');
    if (!is_string($schema) || '' === $schema) {
      return;
    }

    $foreign_key = $this->connection->fetchOne(
      <<<'SQL'
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = :schema
          AND TABLE_NAME = :table
          AND COLUMN_NAME = :column
          AND REFERENCED_TABLE_NAME = :referenced_table
        LIMIT 1
      SQL,
      [
        'schema' => $schema,
        'table' => $table,
        'column' => $column,
        'referenced_table' => $referenced_table,
      ],
    );

    if (!is_string($foreign_key) || '' === $foreign_key) {
      return;
    }

    $escaped_foreign_key = str_replace('`', '``', $foreign_key);
    $this->addSql("ALTER TABLE {$table} DROP FOREIGN KEY `{$escaped_foreign_key}`");
  }
}
