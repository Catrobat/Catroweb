<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261115150000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'When an inactive account was mailed that it will be deleted';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user ADD COLUMN IF NOT EXISTS inactivity_warned_at DATETIME DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user DROP COLUMN inactivity_warned_at');
  }
}
