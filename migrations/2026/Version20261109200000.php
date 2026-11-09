<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109200000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add CatroNotification.created_at: notification ids are random UUIDs, so they cannot order a list';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    // Existing rows get the migration time; new rows get their real creation time from the entity.
    // Stored in UTC like the entity writes it (CURRENT_TIMESTAMP would use the server's local zone).
    $this->addSql('ALTER TABLE CatroNotification ADD created_at DATETIME DEFAULT NULL');
    $this->addSql('UPDATE CatroNotification SET created_at = UTC_TIMESTAMP()');
    $this->addSql('ALTER TABLE CatroNotification MODIFY created_at DATETIME NOT NULL');
    $this->addSql('CREATE INDEX notif_user_created_idx ON CatroNotification (user, created_at, id)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX notif_user_created_idx ON CatroNotification');
    $this->addSql('ALTER TABLE CatroNotification DROP created_at');
  }
}
