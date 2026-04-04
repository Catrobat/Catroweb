<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403140000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Create project_asset and project_asset_mapping tables for content-addressable asset storage';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('
      CREATE TABLE project_asset (
        hash VARCHAR(64) NOT NULL,
        size BIGINT NOT NULL,
        mime_type VARCHAR(127) NOT NULL,
        reference_count INT NOT NULL DEFAULT 0,
        storage_path VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
        PRIMARY KEY (hash)
      ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
    ');

    $this->addSql('
      CREATE TABLE project_asset_mapping (
        id INT AUTO_INCREMENT NOT NULL,
        project_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\',
        asset_hash VARCHAR(64) NOT NULL,
        original_filename VARCHAR(255) NOT NULL,
        path_in_zip VARCHAR(512) NOT NULL,
        UNIQUE INDEX project_path_unique (project_id, path_in_zip),
        INDEX mapping_project_idx (project_id),
        INDEX mapping_asset_idx (asset_hash),
        CONSTRAINT FK_mapping_project FOREIGN KEY (project_id) REFERENCES program (id) ON DELETE CASCADE,
        CONSTRAINT FK_mapping_asset FOREIGN KEY (asset_hash) REFERENCES project_asset (hash),
        PRIMARY KEY (id)
      ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
    ');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE project_asset_mapping');
    $this->addSql('DROP TABLE project_asset');
  }
}
