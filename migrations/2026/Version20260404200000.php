<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260404200000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Create featured_banner table for unified featured content (projects, studios, links, images)';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('
      CREATE TABLE featured_banner (
        id INT AUTO_INCREMENT NOT NULL,
        program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\',
        studio_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\',
        type VARCHAR(20) NOT NULL,
        url VARCHAR(255) DEFAULT NULL,
        image_type VARCHAR(255) NOT NULL,
        title VARCHAR(255) DEFAULT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        priority INT NOT NULL DEFAULT 0,
        created_on DATETIME NOT NULL,
        updated_on DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX IDX_featured_banner_program (program_id),
        INDEX IDX_featured_banner_studio (studio_id),
        CONSTRAINT FK_featured_banner_program FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL,
        CONSTRAINT FK_featured_banner_studio FOREIGN KEY (studio_id) REFERENCES studio (id) ON DELETE SET NULL
      ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
    ');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE featured_banner');
  }
}
