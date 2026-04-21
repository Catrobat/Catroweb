<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260421122535 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add duration and timeout tracking columns to cronjob table';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE cronjob ADD duration_seconds INT DEFAULT NULL, ADD timeout_seconds INT DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE cronjob DROP duration_seconds, DROP timeout_seconds');
  }
}
