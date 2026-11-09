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
    $this->addSql('ALTER TABLE CatroNotification ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
    $this->addSql('ALTER TABLE CatroNotification ALTER created_at DROP DEFAULT');
    $this->addSql('CREATE INDEX notif_user_created_idx ON CatroNotification (user, created_at, id)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX notif_user_created_idx ON CatroNotification');
    $this->addSql('ALTER TABLE CatroNotification DROP created_at');
  }
}
