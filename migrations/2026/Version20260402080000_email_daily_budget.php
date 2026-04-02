<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260402080000_email_daily_budget extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Create email_daily_budget table for tracking daily email sending limits (#6501)';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE email_daily_budget (
      id INT AUTO_INCREMENT NOT NULL,
      date DATE NOT NULL,
      total_sent INT NOT NULL DEFAULT 0,
      verification_sent INT NOT NULL DEFAULT 0,
      reset_sent INT NOT NULL DEFAULT 0,
      consent_sent INT NOT NULL DEFAULT 0,
      admin_sent INT NOT NULL DEFAULT 0,
      management_sent INT NOT NULL DEFAULT 0,
      UNIQUE INDEX UNIQ_email_daily_budget_date (date),
      PRIMARY KEY(id)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE email_daily_budget');
  }
}
