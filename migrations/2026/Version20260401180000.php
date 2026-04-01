<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260401180000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add consent_log table for parental consent audit trail';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE consent_log (
      id INT AUTO_INCREMENT NOT NULL,
      user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\',
      action VARCHAR(30) NOT NULL,
      parent_email VARCHAR(255) NOT NULL,
      ip_address VARCHAR(45) DEFAULT NULL,
      created_at DATETIME NOT NULL,
      INDEX idx_consent_log_user (user_id),
      INDEX idx_consent_log_parent_email (parent_email),
      PRIMARY KEY(id),
      CONSTRAINT FK_consent_log_user FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE consent_log');
  }
}
