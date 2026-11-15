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
    return 'Queue table for background jobs (Symfony Messenger, Doctrine transport)';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE messenger_messages');
  }
}
