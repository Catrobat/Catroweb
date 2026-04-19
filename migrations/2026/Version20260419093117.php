<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419093117 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Rename scratch_program table to scratch_project to match entity mapping';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE scratch_program RENAME TO scratch_project');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE scratch_project RENAME TO scratch_program');
  }
}
