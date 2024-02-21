<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211117171810 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE click_statistics');
    $this->addSql('DROP TABLE homepage_click_statistics');
    $this->addSql('ALTER TABLE fos_user DROP country, DROP additional_email');
    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY FK_1D41556A1748903F');
    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY FK_1D41556A7140A621');
    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY FK_1D41556AA76ED395');
    $this->addSql('DROP INDEX IDX_1D41556A7140A621 ON project_downloads');
    $this->addSql('DROP INDEX IDX_1D41556A1748903F ON project_downloads');
    $this->addSql('DROP INDEX IDX_1D41556AA76ED395 ON project_downloads');
    $this->addSql('ALTER TABLE project_downloads ADD user CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', DROP user_id, DROP rec_from_project_id, DROP recommended_by_project_id, DROP ip, DROP country_code, DROP country_name, DROP user_agent, DROP referrer, DROP recommended_by_page_id, DROP locale, DROP user_specific_recommendation');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556A8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('CREATE INDEX IDX_1D41556A8D93D649 ON project_downloads (user)');
    $this->addSql('ALTER TABLE studio_activity CHANGE type type ENUM(\'comment\', \'project\', \'user\')');
    $this->addSql('ALTER TABLE studio_user CHANGE role role ENUM(\'admin\', \'member\'), CHANGE status status ENUM(\'active\', \'banned\', \'pending_request\')');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE click_statistics (id INT AUTO_INCREMENT NOT NULL, tag_id INT DEFAULT NULL, extension_id INT DEFAULT NULL, project_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', rec_from_project_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', user_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', type LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, clicked_at DATETIME NOT NULL, ip LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, country_code LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, country_name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, user_agent VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' COLLATE `utf8mb4_unicode_ci`, referrer VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' COLLATE `utf8mb4_unicode_ci`, scratch_project_id INT DEFAULT NULL, locale VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, user_specific_recommendation TINYINT(1) DEFAULT \'0\', INDEX IDX_D9945A6E3EB8070A (project_id), INDEX IDX_D9945A6E7140A621 (rec_from_project_id), INDEX IDX_D9945A6EBAD26311 (tag_id), INDEX IDX_D9945A6EA76ED395 (user_id), INDEX IDX_D9945A6E812D5EB (extension_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE homepage_click_statistics (id INT AUTO_INCREMENT NOT NULL, project_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', user_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', type LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, clicked_at DATETIME NOT NULL, ip LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, locale VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, user_agent VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' COLLATE `utf8mb4_unicode_ci`, referrer VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' COLLATE `utf8mb4_unicode_ci`, country_code LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, country_name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_99AECB2F3EB8070A (project_id), INDEX IDX_99AECB2FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E3EB8070A FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E7140A621 FOREIGN KEY (rec_from_project_id) REFERENCES project (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E812D5EB FOREIGN KEY (extension_id) REFERENCES extension (id)');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id)');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2F3EB8070A FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2FA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE fos_user ADD country VARCHAR(75) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD additional_email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY FK_1D41556A8D93D649');
    $this->addSql('DROP INDEX IDX_1D41556A8D93D649 ON project_downloads');
    $this->addSql('ALTER TABLE project_downloads ADD rec_from_project_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', ADD recommended_by_project_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', ADD ip LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD country_code LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD country_name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD user_agent VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' COLLATE `utf8mb4_unicode_ci`, ADD referrer VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' COLLATE `utf8mb4_unicode_ci`, ADD recommended_by_page_id INT DEFAULT NULL, ADD locale VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD user_specific_recommendation TINYINT(1) DEFAULT \'0\', CHANGE user user_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556A1748903F FOREIGN KEY (recommended_by_project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556A7140A621 FOREIGN KEY (rec_from_project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('CREATE INDEX IDX_1D41556A7140A621 ON project_downloads (rec_from_project_id)');
    $this->addSql('CREATE INDEX IDX_1D41556A1748903F ON project_downloads (recommended_by_project_id)');
    $this->addSql('CREATE INDEX IDX_1D41556AA76ED395 ON project_downloads (user_id)');
    $this->addSql('ALTER TABLE studio_activity CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('ALTER TABLE studio_user CHANGE role role VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE status status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
  }
}
