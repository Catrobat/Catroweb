<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261115090000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Background jobs: Messenger queue table, and job status rows clients poll (Scratch import, async upload)';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('CREATE TABLE background_job (id CHAR(36) NOT NULL, state VARCHAR(20) NOT NULL, result VARCHAR(36) DEFAULT NULL, error_status INT DEFAULT NULL, error_message VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, type VARCHAR(30) NOT NULL, subject VARCHAR(64) NOT NULL, user_id CHAR(36) DEFAULT NULL, INDEX background_job_type_subject_state_idx (type, subject, state), INDEX background_job_updated_at_idx (updated_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE background_job');
    $this->addSql('DROP TABLE messenger_messages');
  }
}
