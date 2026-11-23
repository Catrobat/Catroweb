<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * A new table only: the old release neither reads nor writes it. Fill it with catro:backfill:brick-usage.
 */
final class Version20261123120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'project_brick_usage: queryable brick counts per project for the docs brick examples';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE project_brick_usage (
      project_id CHAR(36) NOT NULL,
      brick_type VARCHAR(255) NOT NULL,
      count INT DEFAULT 0 NOT NULL,
      INDEX pbu_brick_type_idx (brick_type),
      INDEX IDX_9548A7BC166D1F9C (project_id),
      PRIMARY KEY (project_id, brick_type)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE project_brick_usage ADD CONSTRAINT FK_9548A7BC166D1F9C FOREIGN KEY (project_id) REFERENCES program (id) ON DELETE CASCADE');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE project_brick_usage');
  }
}
