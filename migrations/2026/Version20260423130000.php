<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260423130000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add composite index for notification type filtering';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE INDEX notif_user_type_idx ON CatroNotification (user, notification_type)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX notif_user_type_idx ON CatroNotification');
  }
}
