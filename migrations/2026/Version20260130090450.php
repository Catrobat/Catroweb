<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130090450 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'New simpler media library';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE media_asset (id CHAR(36) NOT NULL, name VARCHAR(300) NOT NULL, description LONGTEXT DEFAULT NULL, file_type VARCHAR(20) NOT NULL, extension VARCHAR(10) NOT NULL, author VARCHAR(255) DEFAULT NULL, downloads INT DEFAULT 0 NOT NULL, active TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, category_id CHAR(36) NOT NULL, INDEX IDX_1DB69EED12469DE2 (category_id), INDEX name_idx (name), INDEX file_type_idx (file_type), INDEX active_idx (active), INDEX downloads_idx (downloads), INDEX created_at_idx (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('CREATE TABLE media_asset_flavor (mediaasset_id CHAR(36) NOT NULL, flavor_id INT NOT NULL, INDEX IDX_3DF911C510457E75 (mediaasset_id), INDEX IDX_3DF911C5FDDA6450 (flavor_id), PRIMARY KEY (mediaasset_id, flavor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('CREATE TABLE media_category (id CHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, priority INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX priority_idx (priority), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('ALTER TABLE media_asset ADD CONSTRAINT FK_1DB69EED12469DE2 FOREIGN KEY (category_id) REFERENCES media_category (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE media_asset_flavor ADD CONSTRAINT FK_3DF911C510457E75 FOREIGN KEY (mediaasset_id) REFERENCES media_asset (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE media_asset_flavor ADD CONSTRAINT FK_3DF911C5FDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE mediapackagecategory_mediapackage DROP FOREIGN KEY `FK_3AA952779CB0B96C`');
    $this->addSql('ALTER TABLE mediapackagecategory_mediapackage DROP FOREIGN KEY `FK_3AA95277E74D4374`');
    $this->addSql('ALTER TABLE mediapackagefile_flavor DROP FOREIGN KEY `FK_F139CC7D1F3493BC`');
    $this->addSql('ALTER TABLE mediapackagefile_flavor DROP FOREIGN KEY `FK_F139CC7DFDDA6450`');
    $this->addSql('ALTER TABLE media_package_file DROP FOREIGN KEY `FK_5E23F95412469DE2`');
    $this->addSql('DROP TABLE mediapackagecategory_mediapackage');
    $this->addSql('DROP TABLE mediapackagefile_flavor');
    $this->addSql('DROP TABLE media_package');
    $this->addSql('DROP TABLE media_package_category');
    $this->addSql('DROP TABLE media_package_file');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE mediapackagecategory_mediapackage (mediapackagecategory_id INT NOT NULL, mediapackage_id INT NOT NULL, INDEX IDX_3AA95277E74D4374 (mediapackagecategory_id), INDEX IDX_3AA952779CB0B96C (mediapackage_id), PRIMARY KEY (mediapackagecategory_id, mediapackage_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE mediapackagefile_flavor (mediapackagefile_id INT NOT NULL, flavor_id INT NOT NULL, INDEX IDX_F139CC7DFDDA6450 (flavor_id), INDEX IDX_F139CC7D1F3493BC (mediapackagefile_id), PRIMARY KEY (mediapackagefile_id, flavor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE media_package (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, nameUrl LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE media_package_category (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, priority INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE media_package_file (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, extension VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, url LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, active TINYINT NOT NULL, downloads INT NOT NULL, author VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_5E23F95412469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('ALTER TABLE mediapackagecategory_mediapackage ADD CONSTRAINT `FK_3AA952779CB0B96C` FOREIGN KEY (mediapackage_id) REFERENCES media_package (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE mediapackagecategory_mediapackage ADD CONSTRAINT `FK_3AA95277E74D4374` FOREIGN KEY (mediapackagecategory_id) REFERENCES media_package_category (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE mediapackagefile_flavor ADD CONSTRAINT `FK_F139CC7D1F3493BC` FOREIGN KEY (mediapackagefile_id) REFERENCES media_package_file (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE mediapackagefile_flavor ADD CONSTRAINT `FK_F139CC7DFDDA6450` FOREIGN KEY (flavor_id) REFERENCES flavor (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE media_package_file ADD CONSTRAINT `FK_5E23F95412469DE2` FOREIGN KEY (category_id) REFERENCES media_package_category (id)');
    $this->addSql('ALTER TABLE media_asset DROP FOREIGN KEY FK_1DB69EED12469DE2');
    $this->addSql('ALTER TABLE media_asset_flavor DROP FOREIGN KEY FK_3DF911C510457E75');
    $this->addSql('ALTER TABLE media_asset_flavor DROP FOREIGN KEY FK_3DF911C5FDDA6450');
    $this->addSql('DROP TABLE media_asset');
    $this->addSql('DROP TABLE media_asset_flavor');
    $this->addSql('DROP TABLE media_category');
  }
}
