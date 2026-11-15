<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261115143000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'When the admins of an empty, inactive studio were warned that it will be deleted';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE studio ADD COLUMN IF NOT EXISTS deletion_warned_at DATETIME DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE studio DROP COLUMN deletion_warned_at');
  }
}
