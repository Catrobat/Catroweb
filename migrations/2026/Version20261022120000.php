<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Compare asset paths case-sensitively: B.png and b.png in one project are two files';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE project_asset_mapping MODIFY path_in_zip VARCHAR(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->throwIrreversibleMigrationException('Once B.png and b.png are stored side by side, the case-insensitive unique key cannot be restored.');
  }
}
