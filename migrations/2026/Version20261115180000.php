<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rows already hidden get no timestamp here: catro:moderation:purge-hidden stamps them on its first --apply run,
 * so nothing hidden before the deploy is deleted earlier than 14 days after it.
 */
final class Version20261115180000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Since when content is hidden and an account is banned, for the moderation purge';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE program ADD COLUMN IF NOT EXISTS auto_hidden_at DATETIME DEFAULT NULL');
    $this->addSql('ALTER TABLE studio ADD COLUMN IF NOT EXISTS auto_hidden_at DATETIME DEFAULT NULL');
    $this->addSql('ALTER TABLE user_comment ADD COLUMN IF NOT EXISTS auto_hidden_at DATETIME DEFAULT NULL');
    $this->addSql('ALTER TABLE fos_user ADD COLUMN IF NOT EXISTS profile_hidden_at DATETIME DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE program DROP COLUMN auto_hidden_at');
    $this->addSql('ALTER TABLE studio DROP COLUMN auto_hidden_at');
    $this->addSql('ALTER TABLE user_comment DROP COLUMN auto_hidden_at');
    $this->addSql('ALTER TABLE fos_user DROP COLUMN profile_hidden_at');
  }
}
