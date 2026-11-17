<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The usage columns are unmapped now but stay one more release: the previous release still selects and writes them
 * (deploy window, rollback). The defaults let this release insert rows without them. Version20261118120000 drops them.
 */
final class Version20261118090000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'One cached project translation per language pair; defaults for the unmapped usage columns';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql("ALTER TABLE project_machine_translation ALTER provider SET DEFAULT 'libretranslate', ALTER usage_count SET DEFAULT 1, ALTER usage_per_month SET DEFAULT 1, ALTER last_modified_at SET DEFAULT '1970-01-01 00:00:00', ALTER created_at SET DEFAULT '1970-01-01 00:00:00'");
    $this->addSql('DELETE older FROM project_machine_translation older JOIN project_machine_translation newer ON newer.project_id = older.project_id AND newer.source_language = older.source_language AND newer.target_language = older.target_language AND newer.id > older.id');
    $this->addSql('CREATE UNIQUE INDEX uniq_project_machine_translation_languages ON project_machine_translation (project_id, source_language, target_language)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX uniq_project_machine_translation_languages ON project_machine_translation');
    $this->addSql('ALTER TABLE project_machine_translation ALTER provider DROP DEFAULT, ALTER usage_count DROP DEFAULT, ALTER usage_per_month DROP DEFAULT, ALTER last_modified_at DROP DEFAULT, ALTER created_at DROP DEFAULT');
  }
}
