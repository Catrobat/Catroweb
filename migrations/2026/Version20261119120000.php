<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Nullable: other notifications and the rows of the previous release have no reason.
 */
final class Version20261119120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Moderation notifications: why the content was hidden or its owner warned';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE CatroNotification ADD moderation_reason JSON DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE CatroNotification DROP moderation_reason');
  }
}
