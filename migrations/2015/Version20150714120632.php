<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150714120632 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE TABLE fos_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, date_of_birth DATETIME DEFAULT NULL, firstname VARCHAR(64) DEFAULT NULL, lastname VARCHAR(64) DEFAULT NULL, website VARCHAR(64) DEFAULT NULL, biography VARCHAR(1000) DEFAULT NULL, gender VARCHAR(1) DEFAULT NULL, locale VARCHAR(8) DEFAULT NULL, timezone VARCHAR(64) DEFAULT NULL, phone VARCHAR(64) DEFAULT NULL, facebook_uid VARCHAR(255) DEFAULT NULL, facebook_name VARCHAR(255) DEFAULT NULL, facebook_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', twitter_uid VARCHAR(255) DEFAULT NULL, twitter_name VARCHAR(255) DEFAULT NULL, twitter_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', gplus_uid VARCHAR(255) DEFAULT NULL, gplus_name VARCHAR(255) DEFAULT NULL, gplus_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', token VARCHAR(255) DEFAULT NULL, two_step_code VARCHAR(255) DEFAULT NULL, upload_token VARCHAR(300) DEFAULT NULL, avatar LONGTEXT DEFAULT NULL, country VARCHAR(5) DEFAULT \'\' NOT NULL, additional_email VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE fos_user_user_group (user_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_B3C77447A76ED395 (user_id), INDEX IDX_B3C77447FE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, remix_id INT DEFAULT NULL, approved_by_user INT DEFAULT NULL, category_id INT DEFAULT NULL, name VARCHAR(300) NOT NULL, description LONGTEXT NOT NULL, version INT DEFAULT 1 NOT NULL, views INT NOT NULL, downloads INT NOT NULL, directory_hash VARCHAR(255) DEFAULT NULL, uploaded_at DATETIME NOT NULL, last_modified_at DATETIME NOT NULL, language_version VARCHAR(255) DEFAULT \'0\' NOT NULL, catrobat_version_name VARCHAR(255) DEFAULT \'\' NOT NULL, catrobat_version INT DEFAULT 0 NOT NULL, upload_ip VARCHAR(255) DEFAULT \'\' NOT NULL, visible TINYINT(1) DEFAULT \'1\' NOT NULL, flavor VARCHAR(255) DEFAULT \'pocketcode\' NOT NULL, upload_language VARCHAR(255) DEFAULT \'\' NOT NULL, filesize INT DEFAULT 0 NOT NULL, remix_count INT DEFAULT 0 NOT NULL, approved TINYINT(1) DEFAULT \'0\' NOT NULL, apk_status SMALLINT DEFAULT 0 NOT NULL, apk_request_time DATETIME DEFAULT NULL, apk_downloads INT DEFAULT 0 NOT NULL, phiro TINYINT(1) DEFAULT \'0\' NOT NULL, lego TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_92ED7784A76ED395 (user_id), INDEX IDX_92ED7784451AB72F (remix_id), INDEX IDX_92ED77849D8F32D0 (approved_by_user), INDEX IDX_92ED778412469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE fos_user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_583D1F3E5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE starter_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, alias VARCHAR(255) NOT NULL, order_pos INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE Notification (id INT AUTO_INCREMENT NOT NULL, user INT NOT NULL, upload TINYINT(1) NOT NULL, report TINYINT(1) NOT NULL, summary TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_A765AD328D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE featured (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, imagetype VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, active TINYINT(1) NOT NULL, flavor VARCHAR(255) DEFAULT \'pocketcode\' NOT NULL, INDEX IDX_3C1359D43EB8070A (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE ProjectInappropriateReport (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, project_id INT DEFAULT NULL, note LONGTEXT NOT NULL, time DATETIME NOT NULL, state INT NOT NULL, projectVersion INT NOT NULL, INDEX IDX_ED222248A76ED395 (user_id), INDEX IDX_ED2222483EB8070A (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE rudewords (id INT AUTO_INCREMENT NOT NULL, word VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE acl_classes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_type VARCHAR(200) NOT NULL, UNIQUE INDEX UNIQ_69DD750638A36066 (class_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE acl_security_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, identifier VARCHAR(200) NOT NULL, username TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8835EE78772E836AF85E0677 (identifier, username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE acl_object_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, parent_object_identity_id INT UNSIGNED DEFAULT NULL, class_id INT UNSIGNED NOT NULL, object_identifier VARCHAR(100) NOT NULL, entries_inheriting TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_9407E5494B12AD6EA000B10 (object_identifier, class_id), INDEX IDX_9407E54977FA751A (parent_object_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE acl_object_identity_ancestors (object_identity_id INT UNSIGNED NOT NULL, ancestor_id INT UNSIGNED NOT NULL, INDEX IDX_825DE2993D9AB4A6 (object_identity_id), INDEX IDX_825DE299C671CEA1 (ancestor_id), PRIMARY KEY(object_identity_id, ancestor_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE acl_entries (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_id INT UNSIGNED NOT NULL, object_identity_id INT UNSIGNED DEFAULT NULL, security_identity_id INT UNSIGNED NOT NULL, field_name VARCHAR(50) DEFAULT NULL, ace_order SMALLINT UNSIGNED NOT NULL, mask INT NOT NULL, granting TINYINT(1) NOT NULL, granting_strategy VARCHAR(30) NOT NULL, audit_success TINYINT(1) NOT NULL, audit_failure TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4 (class_id, object_identity_id, field_name, ace_order), INDEX IDX_46C8B806EA000B103D9AB4A6DF9183C9 (class_id, object_identity_id, security_identity_id), INDEX IDX_46C8B806EA000B10 (class_id), INDEX IDX_46C8B8063D9AB4A6 (object_identity_id), INDEX IDX_46C8B806DF9183C9 (security_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447FE54D947 FOREIGN KEY (group_id) REFERENCES fos_user_group (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED7784A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED7784451AB72F FOREIGN KEY (remix_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED77849D8F32D0 FOREIGN KEY (approved_by_user) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED778412469DE2 FOREIGN KEY (category_id) REFERENCES starter_category (id)');
    $this->addSql('ALTER TABLE Notification ADD CONSTRAINT FK_A765AD328D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE featured ADD CONSTRAINT FK_3C1359D43EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE ProjectInappropriateReport ADD CONSTRAINT FK_ED222248A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE ProjectInappropriateReport ADD CONSTRAINT FK_ED2222483EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE acl_object_identities ADD CONSTRAINT FK_9407E54977FA751A FOREIGN KEY (parent_object_identity_id) REFERENCES acl_object_identities (id)');
    $this->addSql('ALTER TABLE acl_object_identity_ancestors ADD CONSTRAINT FK_825DE2993D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
    $this->addSql('ALTER TABLE acl_object_identity_ancestors ADD CONSTRAINT FK_825DE299C671CEA1 FOREIGN KEY (ancestor_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
    $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B806EA000B10 FOREIGN KEY (class_id) REFERENCES acl_classes (id) ON UPDATE CASCADE ON DELETE CASCADE');
    $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B8063D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
    $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B806DF9183C9 FOREIGN KEY (security_identity_id) REFERENCES acl_security_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE fos_user_user_group DROP FOREIGN KEY FK_B3C77447A76ED395');
    $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_92ED7784A76ED395');
    $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_92ED77849D8F32D0');
    $this->addSql('ALTER TABLE Notification DROP FOREIGN KEY FK_A765AD328D93D649');
    $this->addSql('ALTER TABLE ProjectInappropriateReport DROP FOREIGN KEY FK_ED222248A76ED395');
    $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_92ED7784451AB72F');
    $this->addSql('ALTER TABLE featured DROP FOREIGN KEY FK_3C1359D43EB8070A');
    $this->addSql('ALTER TABLE ProjectInappropriateReport DROP FOREIGN KEY FK_ED2222483EB8070A');
    $this->addSql('ALTER TABLE fos_user_user_group DROP FOREIGN KEY FK_B3C77447FE54D947');
    $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_92ED778412469DE2');
    $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B806EA000B10');
    $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B806DF9183C9');
    $this->addSql('ALTER TABLE acl_object_identities DROP FOREIGN KEY FK_9407E54977FA751A');
    $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE2993D9AB4A6');
    $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE299C671CEA1');
    $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B8063D9AB4A6');
    $this->addSql('DROP TABLE fos_user');
    $this->addSql('DROP TABLE fos_user_user_group');
    $this->addSql('DROP TABLE project');
    $this->addSql('DROP TABLE fos_user_group');
    $this->addSql('DROP TABLE starter_category');
    $this->addSql('DROP TABLE Notification');
    $this->addSql('DROP TABLE featured');
    $this->addSql('DROP TABLE ProjectInappropriateReport');
    $this->addSql('DROP TABLE rudewords');
    $this->addSql('DROP TABLE acl_classes');
    $this->addSql('DROP TABLE acl_security_identities');
    $this->addSql('DROP TABLE acl_object_identities');
    $this->addSql('DROP TABLE acl_object_identity_ancestors');
    $this->addSql('DROP TABLE acl_entries');
  }
}
