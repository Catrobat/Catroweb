<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303140000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Fix appeal uniqueness constraint: remove state column from unique index to prevent unlimited re-appeals';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE content_appeal DROP INDEX unique_pending_appeal');
    $this->addSql('CREATE UNIQUE INDEX unique_content_appeal ON content_appeal (content_type, content_id, appellant_id)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE content_appeal DROP INDEX unique_content_appeal');
    $this->addSql('CREATE UNIQUE INDEX unique_pending_appeal ON content_appeal (content_type, content_id, appellant_id, state)');
  }
}
