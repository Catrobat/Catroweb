<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add weekly user ranking score and index both ranking columns for the leaderboard';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user ADD ranking_score_recent INT DEFAULT 0 NOT NULL');
    $this->addSql('CREATE INDEX ranking_score_idx ON fos_user (ranking_score)');
    $this->addSql('CREATE INDEX ranking_score_recent_idx ON fos_user (ranking_score_recent)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX ranking_score_recent_idx ON fos_user');
    $this->addSql('DROP INDEX ranking_score_idx ON fos_user');
    $this->addSql('ALTER TABLE fos_user DROP ranking_score_recent');
  }
}
