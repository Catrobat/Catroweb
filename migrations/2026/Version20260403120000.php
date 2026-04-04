<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Remove APK generation columns from program table';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE program DROP COLUMN apk_status, DROP COLUMN apk_request_time, DROP COLUMN apk_downloads');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE program ADD apk_status SMALLINT DEFAULT 0 NOT NULL, ADD apk_request_time DATETIME DEFAULT NULL, ADD apk_downloads INT DEFAULT 0 NOT NULL');
  }
}
