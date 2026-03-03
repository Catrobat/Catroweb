<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304080000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add unique constraint on content_report (reporter_id, content_type, content_id) to prevent duplicate reports at DB level';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE UNIQUE INDEX unique_user_report ON content_report (reporter_id, content_type, content_id)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX unique_user_report ON content_report');
  }
}
