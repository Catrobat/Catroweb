<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261020200000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add credentials_changed_at to users to reject JWTs issued before a credential change (#7402)';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user ADD credentials_changed_at DATETIME DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user DROP credentials_changed_at');
  }
}
