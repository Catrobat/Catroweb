<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260330120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Marker for text sanitization feature (#6334). Run catro:moderation:sanitize-existing to sanitize existing content.';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    // No schema changes needed. The text sanitization is applied at the API boundary.
    // Run `bin/console catro:moderation:sanitize-existing` to sanitize existing content.
    $this->addSql("SELECT 1 -- text-sanitization-marker");
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    // Nothing to reverse — sanitization is a one-way content transformation
  }
}
