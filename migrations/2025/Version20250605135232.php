<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250605135232 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Removing deprecated fields: downloads from Statistic, upload_token from fos_user';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql(<<<'SQL'
            ALTER TABLE Statistic DROP IF EXISTS downloads
        SQL);
    $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS upload_token_idx ON fos_user
        SQL);
    $this->addSql(<<<'SQL'
            ALTER TABLE fos_user DROP IF EXISTS upload_token
        SQL);
  }

  #[\Override]
  public function down(Schema $schema): void
  {
  }
}
