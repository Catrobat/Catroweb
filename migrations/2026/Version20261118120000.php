<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * New columns are nullable or defaulted: the previous release inserts without them, and its rows (auto-detected, no
 * hash) count as stale. The usage columns of the removed providers go: the previous release no longer maps them.
 */
final class Version20261118120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Project translation cache: hash of the translated texts, auto-detected flag; drop the old usage columns';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE project_machine_translation ADD source_hash VARCHAR(32) DEFAULT NULL, ADD auto_detected TINYINT(1) DEFAULT 1 NOT NULL, DROP provider, DROP usage_count, DROP usage_per_month, DROP last_modified_at, DROP created_at');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql("ALTER TABLE project_machine_translation DROP source_hash, DROP auto_detected, ADD provider VARCHAR(255) DEFAULT 'libretranslate' NOT NULL, ADD usage_count INT DEFAULT 1 NOT NULL, ADD usage_per_month DOUBLE PRECISION DEFAULT 1 NOT NULL, ADD last_modified_at DATETIME DEFAULT '1970-01-01 00:00:00' NOT NULL, ADD created_at DATETIME DEFAULT '1970-01-01 00:00:00' NOT NULL");
  }
}
