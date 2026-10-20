<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261020220000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Reset ambiguous legacy credential cutoffs to a single UTC instant (#7422)';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    // Legacy DATETIME values have no timezone. Never guess the offset of an ambiguous DST hour.
    // One statement uses one UTC instant and conservatively invalidates older access tokens.
    $this->addSql('UPDATE fos_user SET credentials_changed_at = UTC_TIMESTAMP() WHERE credentials_changed_at IS NOT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->throwIrreversibleMigrationException('Historic local credential cutoffs cannot be reconstructed safely. Keep UTC-aware readers and writers.');
  }
}
