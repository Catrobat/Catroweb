<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migrate all API-exposed entity PKs from INT AUTO_INCREMENT to UUID (CHAR(36)).
 *
 * This migration is written to be resumable/idempotent across partially migrated
 * databases (e.g. a failed previous deploy).
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
    $this->migrateStudioActivity();
    $this->migrateUserComment();
    $this->migrateCatroNotification();
    $this->migrateAchievement();
    $this->migrateSimpleTables();
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->throwIrreversibleMigrationException('UUID migration cannot be reversed - data would be lost.');
  }

  private function migrateStudioActivity(): void
  {
    $this->dropForeignKeyByReference('studio_user', 'activity', 'studio_activity');
    $this->dropForeignKeyByReference('studio_program', 'activity', 'studio_activity');
    $this->dropForeignKeyByReference('user_comment', 'activity', 'studio_activity');

    $this->ensureColumn('studio_activity', 'uuid_new', 'CHAR(36) DEFAULT NULL');

    $this->addSql(
      "UPDATE studio_activity
       SET uuid_new = CASE
         WHEN uuid_new IS NOT NULL THEN uuid_new
         WHEN CHAR_LENGTH(id) = 36 THEN id
         ELSE UUID()
       END"
    );

    $this->addSql('ALTER TABLE studio_user MODIFY activity CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE studio_program MODIFY activity CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE user_comment MODIFY activity CHAR(36) DEFAULT NULL');

    $this->addSql(
      "UPDATE studio_user su
       JOIN studio_activity sa ON CAST(su.activity AS CHAR(36)) = sa.id
       SET su.activity = sa.uuid_new"
    );
    $this->addSql(
      "UPDATE studio_program sp
       JOIN studio_activity sa ON CAST(sp.activity AS CHAR(36)) = sa.id
       SET sp.activity = sa.uuid_new"
    );
    $this->addSql(
      "UPDATE user_comment uc
       JOIN studio_activity sa ON CAST(uc.activity AS CHAR(36)) = sa.id
       SET uc.activity = sa.uuid_new
       WHERE uc.activity IS NOT NULL"
    );

    $this->addSql('ALTER TABLE studio_activity MODIFY id CHAR(36) NOT NULL');
    $this->addSql('UPDATE studio_activity SET id = uuid_new WHERE id <> uuid_new');

    $this->dropColumnIfExists('studio_activity', 'uuid_new');

    $this->addSql('ALTER TABLE studio_user ADD CONSTRAINT FK_EC686DD1AC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_program ADD CONSTRAINT FK_4CB3C24AAC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66AC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');
  }

  private function migrateUserComment(): void
  {
    $this->dropForeignKeyByReference('CatroNotification', 'comment_id', 'user_comment');
    $this->dropForeignKeyByReference('user_comment_machine_translation', 'comment_id', 'user_comment');

    $this->addSql("UPDATE user_comment SET parent_id = NULL WHERE parent_id = '0' OR parent_id = ''");

    $this->ensureColumn('user_comment', 'uuid_new', 'CHAR(36) DEFAULT NULL');

    $this->addSql(
      "UPDATE user_comment
       SET uuid_new = CASE
         WHEN uuid_new IS NOT NULL THEN uuid_new
         WHEN CHAR_LENGTH(id) = 36 THEN id
         ELSE UUID()
       END"
    );

    $this->addSql('ALTER TABLE user_comment MODIFY parent_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE CatroNotification MODIFY comment_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE user_comment_machine_translation MODIFY comment_id CHAR(36) DEFAULT NULL');

    $this->addSql(
      "UPDATE user_comment child
       JOIN user_comment parent ON CAST(child.parent_id AS CHAR(36)) = parent.id
       SET child.parent_id = parent.uuid_new
       WHERE child.parent_id IS NOT NULL"
    );
    $this->addSql(
      "UPDATE CatroNotification cn
       JOIN user_comment uc ON CAST(cn.comment_id AS CHAR(36)) = uc.id
       SET cn.comment_id = uc.uuid_new
       WHERE cn.comment_id IS NOT NULL"
    );
    $this->addSql(
      "UPDATE user_comment_machine_translation ucmt
       JOIN user_comment uc ON CAST(ucmt.comment_id AS CHAR(36)) = uc.id
       SET ucmt.comment_id = uc.uuid_new
       WHERE ucmt.comment_id IS NOT NULL"
    );

    $this->addSql('ALTER TABLE user_comment MODIFY id CHAR(36) NOT NULL');
    $this->addSql('UPDATE user_comment SET id = uuid_new WHERE id <> uuid_new');

    $this->dropColumnIfExists('user_comment', 'uuid_new');

    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAF8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE user_comment_machine_translation ADD CONSTRAINT FK_2CEF8196F8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id) ON DELETE CASCADE');
  }

  private function migrateCatroNotification(): void
  {
    $this->addSql('ALTER TABLE CatroNotification MODIFY id CHAR(36) NOT NULL');
    $this->addSql('UPDATE CatroNotification SET id = UUID() WHERE CHAR_LENGTH(id) < 36');
  }

  private function migrateAchievement(): void
  {
    $this->dropForeignKeyByReference('user_achievement', 'achievement', 'achievement');

    $this->ensureColumn('achievement', 'uuid_new', 'CHAR(36) DEFAULT NULL');

    $this->addSql(
      "UPDATE achievement
       SET uuid_new = CASE
         WHEN uuid_new IS NOT NULL THEN uuid_new
         WHEN CHAR_LENGTH(id) = 36 THEN id
         ELSE UUID()
       END"
    );

    $this->addSql('ALTER TABLE user_achievement MODIFY achievement CHAR(36) NOT NULL');

    $this->addSql(
      "UPDATE user_achievement ua
       JOIN achievement a ON CAST(ua.achievement AS CHAR(36)) = a.id
       SET ua.achievement = a.uuid_new"
    );

    $this->addSql('ALTER TABLE achievement MODIFY id CHAR(36) NOT NULL');
    $this->addSql('UPDATE achievement SET id = uuid_new WHERE id <> uuid_new');

    $this->dropColumnIfExists('achievement', 'uuid_new');

    $this->addSql('ALTER TABLE user_achievement ADD CONSTRAINT FK_3F68B66496737FF1 FOREIGN KEY (achievement) REFERENCES achievement (id) ON DELETE CASCADE');

    $this->addSql('ALTER TABLE user_achievement MODIFY id CHAR(36) NOT NULL');
    $this->addSql('UPDATE user_achievement SET id = UUID() WHERE CHAR_LENGTH(id) < 36');
  }

  private function migrateSimpleTables(): void
  {
    foreach (['content_report', 'content_appeal', 'studio_user', 'studio_join_requests', 'featured_banner'] as $table) {
      $this->addSql("ALTER TABLE {$table} MODIFY id CHAR(36) NOT NULL");
      $this->addSql("UPDATE {$table} SET id = UUID() WHERE CHAR_LENGTH(id) < 36");
    }
  }

  private function ensureColumn(string $table, string $column, string $definition): void
  {
    if ($this->columnExists($table, $column)) {
      return;
    }

    $this->addSql("ALTER TABLE {$table} ADD {$column} {$definition}");
  }

  private function dropColumnIfExists(string $table, string $column): void
  {
    if (!$this->columnExists($table, $column)) {
      return;
    }

    $this->addSql("ALTER TABLE {$table} DROP COLUMN {$column}");
  }

  private function columnExists(string $table, string $column): bool
  {
    $schema = $this->connection->fetchOne('SELECT DATABASE()');
    if (!is_string($schema) || '' === $schema) {
      return false;
    }

    $exists = $this->connection->fetchOne(
      <<<'SQL'
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = :schema
          AND TABLE_NAME = :table
          AND COLUMN_NAME = :column
        LIMIT 1
      SQL,
      [
        'schema' => $schema,
        'table' => $table,
        'column' => $column,
      ],
    );

    return false !== $exists;
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
