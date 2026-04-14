<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds video_url and flavors columns to featured_banner.
 */
final class Version20260414120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add video_url and flavors columns to featured_banner table';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE featured_banner ADD video_url VARCHAR(255) DEFAULT NULL, ADD flavors JSON DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE featured_banner DROP video_url, DROP flavors');
  }
}
