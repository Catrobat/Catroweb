<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds fos_user.avatar_key for the responsive-variant avatar storage.
 *
 * The legacy base64 `avatar` TEXT column is intentionally left in place for
 * one release so read-through works during rollout and
 * `bin/console catro:migrate:avatars` can backfill variants from it. A
 * follow-up migration will drop the legacy column once the backfill is
 * complete on every environment.
 */
final class Version20260413120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add fos_user.avatar_key for responsive AVIF/WebP avatar variant storage (#6628).';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user ADD avatar_key VARCHAR(200) DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user DROP avatar_key');
  }
}
