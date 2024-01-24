<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210718210504 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'add project custom translation';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE project_custom_translation (id INT AUTO_INCREMENT NOT NULL, project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', language VARCHAR(5) NOT NULL, name VARCHAR(300) DEFAULT NULL, description LONGTEXT DEFAULT NULL, credits LONGTEXT DEFAULT NULL, INDEX IDX_34070EC4166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE project_custom_translation ADD CONSTRAINT FK_34070EC4166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE project_custom_translation');
  }
}
