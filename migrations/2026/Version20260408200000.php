<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260408200000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add missing columns for ProjectExpiringNotification and ProjectDeletedNotification entities';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE CatroNotification ADD expiry_days INT DEFAULT NULL, ADD deleted_project_name VARCHAR(255) DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE CatroNotification DROP expiry_days, DROP deleted_project_name');
  }
}
