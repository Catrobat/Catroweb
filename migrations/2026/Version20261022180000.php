<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022180000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Drop project_asset.reference_count: an asset\'s references are its mappings';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE project_asset DROP reference_count');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE project_asset ADD reference_count INT NOT NULL DEFAULT 0');
    $this->addSql('UPDATE project_asset a SET reference_count = (SELECT COUNT(*) FROM project_asset_mapping m WHERE m.asset_hash = a.hash)');
  }
}
