<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191108112739 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Credits and CatroNotification Hotfix';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE project CHANGE credits credits LONGTEXT DEFAULT NULL');
    $this->addSql('ALTER TABLE CatroNotification ADD seen TINYINT(1) DEFAULT \'0\' NOT NULL');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE CatroNotification DROP seen');
    $this->addSql('ALTER TABLE project CHANGE credits credits LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci');
  }
}
