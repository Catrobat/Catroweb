<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * A new table, so the release before this one never writes to it and needs no defaults.
 */
final class Version20261123143700 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'User docs pages synced from the Catrobat/docs repo';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE doc_page (id CHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(500) DEFAULT NULL, sort_order INT NOT NULL, flavors JSON NOT NULL, html MEDIUMTEXT NOT NULL, toc JSON NOT NULL, content_hash VARCHAR(64) NOT NULL, source_hash VARCHAR(64) DEFAULT NULL, translation_state VARCHAR(10) NOT NULL, reviewed TINYINT NOT NULL, updated_at DATETIME NOT NULL, kind VARCHAR(10) NOT NULL, path VARCHAR(191) NOT NULL, locale VARCHAR(16) NOT NULL, source_file VARCHAR(255) NOT NULL, UNIQUE INDEX doc_page_kind_path_locale_idx (kind, path, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE doc_page');
  }
}
