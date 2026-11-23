<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * A new table, so the release before this one never writes to it and needs no defaults.
 */
final class Version20261124090000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Docs search vectors, so a docs sync only embeds new and changed pages';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE doc_embedding (text_hash VARCHAR(32) NOT NULL, vector JSON NOT NULL, PRIMARY KEY (text_hash)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE doc_embedding');
  }
}
