<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260404160000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Create featured_studio table for featured studios on homepage';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('
      CREATE TABLE featured_studio (
        id INT AUTO_INCREMENT NOT NULL,
        studio_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\',
        imagetype VARCHAR(255) NOT NULL,
        url VARCHAR(255) DEFAULT NULL,
        active TINYINT(1) NOT NULL,
        priority INT NOT NULL DEFAULT 0,
        created_on DATETIME NOT NULL,
        updated_on DATETIME DEFAULT NULL,
        INDEX IDX_featured_studio_studio (studio_id),
        CONSTRAINT FK_featured_studio_studio FOREIGN KEY (studio_id) REFERENCES studio (id) ON DELETE CASCADE,
        PRIMARY KEY (id)
      ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
    ');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE featured_studio');
  }
}
