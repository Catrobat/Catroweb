<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190313084056 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Migration to change all known collation to support UTF8MB4';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    $this->addSql('ALTER TABLE CatroNotification CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE GameJam CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE Notification CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE ProjectInappropriateReport CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE click_statistics CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE extension CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE featured CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE fos_user CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE fos_user_group CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE fos_user_user_group CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE gamejams_sampleprojects CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE homepage_click_statistics CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE media_package CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE media_package_category CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE media_package_file CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE mediapackagecategory_mediapackage CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE nolb_example_project CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_downloads CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_extension CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_like CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_remix_backward_relation CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_remix_relation CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_tag CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE rudewords CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE scratch_project CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE scratch_project_remix_relation CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE starter_category CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE tags CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE template CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE user_comment CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE user_like_similarity_relation CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CONVERT TO CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE CatroNotification CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE GameJam CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE Notification CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE ProjectInappropriateReport CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE click_statistics CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE extension CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE featured CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE fos_user CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE fos_user_group CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE fos_user_user_group CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE gamejams_sampleprojects CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE homepage_click_statistics CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE media_package CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE media_package_category CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE media_package_file CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE mediapackagecategory_mediapackage CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE migration_versions CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE nolb_example_project CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_downloads CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_extension CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_like CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_remix_backward_relation CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_remix_relation CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE project_tag CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE rudewords CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE scratch_project CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE scratch_project_remix_relation CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE starter_category CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE tags CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE template CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE user_comment CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE user_like_similarity_relation CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci collate utf8mb4_unicode_ci');
  }
}
