<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260329220000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add scoring version and bonus points to project_code_statistics';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql("ALTER TABLE project_code_statistics ADD score_bonus INT DEFAULT 0 NOT NULL, ADD scoring_version VARCHAR(64) DEFAULT 'rubric_2021_v2' NOT NULL");
    $this->addSql("UPDATE project_code_statistics SET scoring_version = 'legacy_keyword_counts_v1'");
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE project_code_statistics DROP score_bonus, DROP scoring_version');
  }
}
