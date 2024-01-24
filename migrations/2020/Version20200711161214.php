<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200711161214 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE fos_user DROP limited');
    $this->addSql('ALTER TABLE project ADD snapshots_enabled TINYINT(1) DEFAULT \'0\' NOT NULL');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE fos_user ADD limited TINYINT(1) DEFAULT \'0\' NOT NULL');
    $this->addSql('ALTER TABLE project DROP snapshots_enabled');
  }
}
