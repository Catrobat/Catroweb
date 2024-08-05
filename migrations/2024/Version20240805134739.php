<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240805134739 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE studio_activity CHANGE type type VARCHAR(20) NOT NULL');
    $this->addSql('ALTER TABLE studio_user CHANGE role role VARCHAR(20) NOT NULL, CHANGE status status VARCHAR(20) NOT NULL');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE studio_user CHANGE role role VARCHAR(0) DEFAULT NULL, CHANGE status status VARCHAR(0) DEFAULT NULL');
    $this->addSql('ALTER TABLE studio_activity CHANGE type type VARCHAR(0) DEFAULT NULL');
  }
}
