<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Nullable: community reports and the rows of the previous release have no flagged assets.
 */
final class Version20261119090000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Content reports: the images and text signals an automatic flag found';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE content_report ADD flagged_assets JSON DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE content_report DROP flagged_assets');
  }
}
