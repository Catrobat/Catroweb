<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260328140000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add email notification preference to users and created_at/email_sent to notifications for digest system';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user ADD emailNotificationPreference VARCHAR(20) NOT NULL DEFAULT \'immediate\'');
    $this->addSql('ALTER TABLE CatroNotification ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    $this->addSql('ALTER TABLE CatroNotification ADD email_sent TINYINT(1) NOT NULL DEFAULT 0');
    $this->addSql('CREATE INDEX notif_email_pending_idx ON CatroNotification (email_sent, created_at)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX notif_email_pending_idx ON CatroNotification');
    $this->addSql('ALTER TABLE CatroNotification DROP COLUMN email_sent');
    $this->addSql('ALTER TABLE CatroNotification DROP COLUMN created_at');
    $this->addSql('ALTER TABLE fos_user DROP COLUMN emailNotificationPreference');
  }
}
