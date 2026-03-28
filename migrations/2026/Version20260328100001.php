<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260328100000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add has_missing_files flag to program table for broken project detection';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE program ADD has_missing_files TINYINT(1) DEFAULT 0 NOT NULL');
    $this->addSql('CREATE INDEX has_missing_files_idx ON program (has_missing_files)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX has_missing_files_idx ON program');
    $this->addSql('ALTER TABLE program DROP has_missing_files');
  }
}
