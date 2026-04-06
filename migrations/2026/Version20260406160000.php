<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260406160000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add storage_protected flag to program table for retention whitelisting';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE program ADD storage_protected TINYINT(1) DEFAULT 0 NOT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE program DROP storage_protected');
  }
}
