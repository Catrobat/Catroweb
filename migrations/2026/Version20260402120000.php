<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260402120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Remove deprecated Google+ token fields (gplus_id_token, gplus_refresh_token) from fos_user';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user DROP COLUMN gplus_id_token, DROP COLUMN gplus_refresh_token');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user ADD gplus_id_token VARCHAR(5000) DEFAULT NULL, ADD gplus_refresh_token VARCHAR(300) DEFAULT NULL');
  }
}
