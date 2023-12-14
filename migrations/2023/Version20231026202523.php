<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231026202523 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE maintanance_information (id INT AUTO_INCREMENT NOT NULL, internalTitle VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, ltmCode VARCHAR(255) DEFAULT NULL, ltmParameters LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE studio_activity CHANGE type type ENUM(\'comment\', \'project\', \'user\')');
    $this->addSql('ALTER TABLE studio_user CHANGE role role ENUM(\'admin\', \'member\'), CHANGE status status ENUM(\'active\', \'banned\', \'pending_request\')');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE maintanance_information');
    $this->addSql('ALTER TABLE studio_activity CHANGE type type VARCHAR(255) DEFAULT NULL');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL');
    $this->addSql('ALTER TABLE studio_user CHANGE role role VARCHAR(255) DEFAULT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL');
  }
}
