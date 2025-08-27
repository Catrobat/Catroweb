<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240916115931 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Creates the Statistic table and inserts a default entry with id 1, projects and users set to 0.';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE Statistic (id INT AUTO_INCREMENT NOT NULL, projects BIGINT NOT NULL, users BIGINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('INSERT INTO Statistic (id, projects, users) VALUES (1, 0, 0)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE Statistic');
  }
}
