<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112180000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add email_daily_budget.provider_sent: mails sent per mail provider, so each stays under its daily limit';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    // Nullable, so the old release (which never writes it) can still insert rows during the deploy.
    $this->addSql('ALTER TABLE email_daily_budget ADD provider_sent JSON DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE email_daily_budget DROP provider_sent');
  }
}
