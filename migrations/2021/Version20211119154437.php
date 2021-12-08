<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211119154437 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556A3EB8070A');
    $this->addSql('ALTER TABLE program_downloads CHANGE program_id program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A3EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556A3EB8070A');
    $this->addSql('ALTER TABLE program_downloads CHANGE program_id program_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
  }
}
