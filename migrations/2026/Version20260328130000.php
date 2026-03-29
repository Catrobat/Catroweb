<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260328130000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Create project_code_statistics table for persisted code statistics';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE project_code_statistics (
      id INT AUTO_INCREMENT NOT NULL,
      program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\',
      created_at DATETIME NOT NULL,
      scenes INT DEFAULT 0 NOT NULL,
      scripts INT DEFAULT 0 NOT NULL,
      bricks INT DEFAULT 0 NOT NULL,
      objects INT DEFAULT 0 NOT NULL,
      looks INT DEFAULT 0 NOT NULL,
      sounds INT DEFAULT 0 NOT NULL,
      global_variables INT DEFAULT 0 NOT NULL,
      local_variables INT DEFAULT 0 NOT NULL,
      script_counts JSON NOT NULL,
      brick_counts JSON NOT NULL,
      score_abstraction INT DEFAULT 0 NOT NULL,
      score_parallelism INT DEFAULT 0 NOT NULL,
      score_synchronization INT DEFAULT 0 NOT NULL,
      score_logical_thinking INT DEFAULT 0 NOT NULL,
      score_flow_control INT DEFAULT 0 NOT NULL,
      score_user_interactivity INT DEFAULT 0 NOT NULL,
      score_data_representation INT DEFAULT 0 NOT NULL,
      INDEX pcs_program_idx (program_id),
      PRIMARY KEY(id)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

    $this->addSql('ALTER TABLE project_code_statistics ADD CONSTRAINT FK_PCS_PROGRAM FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE CASCADE');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE project_code_statistics');
  }
}
