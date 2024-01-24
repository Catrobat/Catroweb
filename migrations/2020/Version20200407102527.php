<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200407102527 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE TABLE example (id INT AUTO_INCREMENT NOT NULL, project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', imagetype VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, active TINYINT(1) NOT NULL, flavor VARCHAR(255) DEFAULT \'pocketcode\' NOT NULL, priority INT NOT NULL, for_ios TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_6EEC9B9F3EB8070A (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE example ADD CONSTRAINT FK_6EEC9B9F3EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('DROP TABLE example');
  }
}
