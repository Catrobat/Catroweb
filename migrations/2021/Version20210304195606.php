<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210304195606 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE CatroNotification (id INT AUTO_INCREMENT NOT NULL, user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', comment_id INT DEFAULT NULL, like_from CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', follower_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', remix_root CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', remix_program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', title VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, seen TINYINT(1) DEFAULT \'0\' NOT NULL, type VARCHAR(255) NOT NULL, notification_type VARCHAR(255) NOT NULL, prize LONGTEXT DEFAULT NULL, image_path LONGTEXT DEFAULT NULL, INDEX IDX_22087FCA8D93D649 (user), INDEX IDX_22087FCAF8697D13 (comment_id), INDEX IDX_22087FCA606F7D0E (like_from), INDEX IDX_22087FCA3EB8070A (program_id), INDEX IDX_22087FCAAC24F853 (follower_id), INDEX IDX_22087FCA3880B495 (remix_root), INDEX IDX_22087FCA63B7B817 (remix_program_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE GameJam (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(300) NOT NULL, form_url VARCHAR(300) DEFAULT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, hashtag VARCHAR(100) DEFAULT NULL, flavor VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gamejams_sampleprograms (gamejam_id INT NOT NULL, program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_8EADA13654B8758D (gamejam_id), INDEX IDX_8EADA1363EB8070A (program_id), PRIMARY KEY(gamejam_id, program_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Notification (id INT AUTO_INCREMENT NOT NULL, user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', upload TINYINT(1) NOT NULL, report TINYINT(1) NOT NULL, summary TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_A765AD328D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ProgramInappropriateReport (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', user_id_rep CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', category TEXT NOT NULL, note LONGTEXT NOT NULL, time DATETIME NOT NULL, state INT NOT NULL, projectVersion INT NOT NULL, INDEX IDX_ED222248A76ED395 (user_id), INDEX IDX_ED2222483EB8070A (program_id), INDEX IDX_ED2222481B4D7895 (user_id_rep), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE click_statistics (id INT AUTO_INCREMENT NOT NULL, tag_id INT DEFAULT NULL, extension_id INT DEFAULT NULL, program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', rec_from_program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', type LONGTEXT NOT NULL, scratch_program_id INT DEFAULT NULL, user_specific_recommendation TINYINT(1) DEFAULT \'0\', clicked_at DATETIME NOT NULL, ip LONGTEXT NOT NULL, country_code LONGTEXT DEFAULT NULL, country_name LONGTEXT DEFAULT NULL, locale VARCHAR(255) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT \'\', referrer VARCHAR(255) DEFAULT \'\', INDEX IDX_D9945A6EBAD26311 (tag_id), INDEX IDX_D9945A6E812D5EB (extension_id), INDEX IDX_D9945A6E3EB8070A (program_id), INDEX IDX_D9945A6E7140A621 (rec_from_program_id), INDEX IDX_D9945A6EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE example (id INT AUTO_INCREMENT NOT NULL, flavor_id INT DEFAULT NULL, program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', imagetype VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, priority INT NOT NULL, for_ios TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_6EEC9B9FFDDA6450 (flavor_id), INDEX IDX_6EEC9B9F3EB8070A (program_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE extension (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, prefix VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE featured (id INT AUTO_INCREMENT NOT NULL, flavor_id INT DEFAULT NULL, program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', url VARCHAR(255) DEFAULT NULL, imagetype VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, priority INT NOT NULL, for_ios TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_3C1359D4FDDA6450 (flavor_id), INDEX IDX_3C1359D43EB8070A (program_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE flavor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_BC2534545E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, date_of_birth DATETIME DEFAULT NULL, firstname VARCHAR(64) DEFAULT NULL, lastname VARCHAR(64) DEFAULT NULL, website VARCHAR(64) DEFAULT NULL, biography VARCHAR(1000) DEFAULT NULL, gender VARCHAR(1) DEFAULT NULL, locale VARCHAR(8) DEFAULT NULL, timezone VARCHAR(64) DEFAULT NULL, phone VARCHAR(64) DEFAULT NULL, facebook_uid VARCHAR(255) DEFAULT NULL, facebook_name VARCHAR(255) DEFAULT NULL, facebook_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', twitter_uid VARCHAR(255) DEFAULT NULL, twitter_name VARCHAR(255) DEFAULT NULL, twitter_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', gplus_uid VARCHAR(255) DEFAULT NULL, gplus_name VARCHAR(255) DEFAULT NULL, gplus_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', token VARCHAR(255) DEFAULT NULL, two_step_code VARCHAR(255) DEFAULT NULL, upload_token VARCHAR(300) DEFAULT NULL, avatar LONGTEXT DEFAULT NULL, country VARCHAR(5) DEFAULT \'\' NOT NULL, additional_email VARCHAR(255) DEFAULT NULL, gplus_access_token VARCHAR(300) DEFAULT NULL, google_id VARCHAR(300) DEFAULT NULL, facebook_id VARCHAR(300) DEFAULT NULL, google_access_token VARCHAR(300) DEFAULT NULL, facebook_access_token VARCHAR(300) DEFAULT NULL, apple_id VARCHAR(300) DEFAULT NULL, apple_access_token VARCHAR(300) DEFAULT NULL, gplus_id_token VARCHAR(5000) DEFAULT NULL, gplus_refresh_token VARCHAR(300) DEFAULT NULL, scratch_user_id INT DEFAULT NULL, oauth_password_created TINYINT(1) DEFAULT \'0\' NOT NULL, oauth_user TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_957A6479C05FB297 (confirmation_token), UNIQUE INDEX UNIQ_957A64797C85A057 (scratch_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_user (user_source CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user_target CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_F7129A803AD8644E (user_source), INDEX IDX_F7129A80233D34C1 (user_target), PRIMARY KEY(user_source, user_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user_user_group (user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', group_id INT NOT NULL, INDEX IDX_B3C77447A76ED395 (user_id), INDEX IDX_B3C77447FE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_583D1F3E5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE homepage_click_statistics (id INT AUTO_INCREMENT NOT NULL, program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', type LONGTEXT NOT NULL, clicked_at DATETIME NOT NULL, ip LONGTEXT NOT NULL, country_code LONGTEXT DEFAULT NULL, country_name LONGTEXT DEFAULT NULL, locale VARCHAR(255) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT \'\', referrer VARCHAR(255) DEFAULT \'\', INDEX IDX_99AECB2F3EB8070A (program_id), INDEX IDX_99AECB2FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_package (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT NOT NULL, nameUrl LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_package_category (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT NOT NULL, priority INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mediapackagecategory_mediapackage (mediapackagecategory_id INT NOT NULL, mediapackage_id INT NOT NULL, INDEX IDX_3AA95277E74D4374 (mediapackagecategory_id), INDEX IDX_3AA952779CB0B96C (mediapackage_id), PRIMARY KEY(mediapackagecategory_id, mediapackage_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_package_file (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name LONGTEXT NOT NULL, extension VARCHAR(255) NOT NULL, url LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, downloads INT NOT NULL, author VARCHAR(255) DEFAULT NULL, INDEX IDX_5E23F95412469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mediapackagefile_flavor (mediapackagefile_id INT NOT NULL, flavor_id INT NOT NULL, INDEX IDX_F139CC7D1F3493BC (mediapackagefile_id), INDEX IDX_F139CC7DFDDA6450 (flavor_id), PRIMARY KEY(mediapackagefile_id, flavor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE program (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', approved_by_user CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', category_id INT DEFAULT NULL, gamejam_id INT DEFAULT NULL, name VARCHAR(300) NOT NULL, description LONGTEXT DEFAULT NULL, credits LONGTEXT DEFAULT NULL, version INT DEFAULT 1 NOT NULL, scratch_id INT DEFAULT NULL, views INT NOT NULL, downloads INT NOT NULL, uploaded_at DATETIME NOT NULL, last_modified_at DATETIME NOT NULL, language_version VARCHAR(255) DEFAULT \'0\' NOT NULL, catrobat_version_name VARCHAR(255) DEFAULT \'\' NOT NULL, catrobat_version INT DEFAULT 0 NOT NULL, upload_ip VARCHAR(255) DEFAULT \'\' NOT NULL, visible TINYINT(1) DEFAULT \'1\' NOT NULL, private TINYINT(1) DEFAULT \'0\' NOT NULL, flavor VARCHAR(255) DEFAULT \'pocketcode\' NOT NULL, upload_language VARCHAR(255) DEFAULT \'\' NOT NULL, filesize INT DEFAULT 0 NOT NULL, remix_root TINYINT(1) DEFAULT \'1\' NOT NULL, remix_migrated_at DATETIME DEFAULT NULL, approved TINYINT(1) DEFAULT \'0\' NOT NULL, apk_status SMALLINT DEFAULT 0 NOT NULL, apk_request_time DATETIME DEFAULT NULL, apk_downloads INT DEFAULT 0 NOT NULL, gamejam_submission_accepted TINYINT(1) DEFAULT \'0\' NOT NULL, gamejam_submission_date DATETIME DEFAULT NULL, debug_build TINYINT(1) DEFAULT \'0\' NOT NULL, snapshots_enabled TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_92ED7784711DBBB4 (scratch_id), INDEX IDX_92ED7784A76ED395 (user_id), INDEX IDX_92ED77849D8F32D0 (approved_by_user), INDEX IDX_92ED778412469DE2 (category_id), INDEX IDX_92ED778454B8758D (gamejam_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE program_tag (program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', tag_id INT NOT NULL, INDEX IDX_88B68E093EB8070A (program_id), INDEX IDX_88B68E09BAD26311 (tag_id), PRIMARY KEY(program_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE program_extension (program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', extension_id INT NOT NULL, INDEX IDX_C985CCA83EB8070A (program_id), INDEX IDX_C985CCA8812D5EB (extension_id), PRIMARY KEY(program_id, extension_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE program_downloads (id INT AUTO_INCREMENT NOT NULL, program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', recommended_by_program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', rec_from_program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', recommended_by_page_id INT DEFAULT NULL, user_specific_recommendation TINYINT(1) DEFAULT \'0\', downloaded_at DATETIME NOT NULL, ip LONGTEXT NOT NULL, country_code LONGTEXT DEFAULT NULL, country_name LONGTEXT DEFAULT NULL, locale VARCHAR(255) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT \'\', referrer VARCHAR(255) DEFAULT \'\', INDEX IDX_1D41556A3EB8070A (program_id), INDEX IDX_1D41556A1748903F (recommended_by_program_id), INDEX IDX_1D41556A7140A621 (rec_from_program_id), INDEX IDX_1D41556AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE program_like (program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', type INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_A18515B43EB8070A (program_id), INDEX IDX_A18515B4A76ED395 (user_id), PRIMARY KEY(program_id, user_id, type)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE program_remix_backward_relation (parent_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', child_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL, seen_at DATETIME DEFAULT NULL, INDEX IDX_C294015B727ACA70 (parent_id), INDEX IDX_C294015BDD62C21B (child_id), PRIMARY KEY(parent_id, child_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE program_remix_relation (ancestor_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', descendant_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', depth INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, seen_at DATETIME DEFAULT NULL, INDEX IDX_E5AD23B4C671CEA1 (ancestor_id), INDEX IDX_E5AD23B41844467D (descendant_id), PRIMARY KEY(ancestor_id, descendant_id, depth)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rudewords (id INT AUTO_INCREMENT NOT NULL, word VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_4C737F87C3F17511 (word), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE scratch_program (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(300) DEFAULT NULL, description LONGTEXT DEFAULT NULL, username LONGTEXT DEFAULT NULL, last_modified_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE scratch_program_remix_relation (scratch_parent_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', catrobat_child_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_3B275E756F212B35 (catrobat_child_id), PRIMARY KEY(scratch_parent_id, catrobat_child_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE starter_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, alias VARCHAR(255) NOT NULL, order_pos INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey (id INT AUTO_INCREMENT NOT NULL, language_code VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, active TINYINT(1) DEFAULT \'1\' NOT NULL, UNIQUE INDEX UNIQ_AD5F9BFC451CDAD4 (language_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, en VARCHAR(255) DEFAULT NULL, de VARCHAR(255) DEFAULT NULL, it VARCHAR(255) DEFAULT NULL, fr VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_comment (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', notification_id INT DEFAULT NULL, uploadDate DATE NOT NULL, text LONGTEXT NOT NULL, username VARCHAR(255) NOT NULL, isReported TINYINT(1) NOT NULL, programId CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_CC794C66A76ED395 (user_id), UNIQUE INDEX UNIQ_CC794C66EF1A9D84 (notification_id), INDEX IDX_CC794C66BB3368CF (programId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_like_similarity_relation (first_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', second_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_132DCA08B4E2BF69 (first_user_id), INDEX IDX_132DCA08B02C53F8 (second_user_id), PRIMARY KEY(first_user_id, second_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_remix_similarity_relation (first_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', second_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_143F09C7B4E2BF69 (first_user_id), INDEX IDX_143F09C7B02C53F8 (second_user_id), PRIMARY KEY(first_user_id, second_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_test_group (user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', group_number INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_classes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_type VARCHAR(200) NOT NULL, UNIQUE INDEX UNIQ_69DD750638A36066 (class_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_security_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, identifier VARCHAR(200) NOT NULL, username TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8835EE78772E836AF85E0677 (identifier, username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_object_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, parent_object_identity_id INT UNSIGNED DEFAULT NULL, class_id INT UNSIGNED NOT NULL, object_identifier VARCHAR(100) NOT NULL, entries_inheriting TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_9407E5494B12AD6EA000B10 (object_identifier, class_id), INDEX IDX_9407E54977FA751A (parent_object_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_object_identity_ancestors (object_identity_id INT UNSIGNED NOT NULL, ancestor_id INT UNSIGNED NOT NULL, INDEX IDX_825DE2993D9AB4A6 (object_identity_id), INDEX IDX_825DE299C671CEA1 (ancestor_id), PRIMARY KEY(object_identity_id, ancestor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_entries (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_id INT UNSIGNED NOT NULL, object_identity_id INT UNSIGNED DEFAULT NULL, security_identity_id INT UNSIGNED NOT NULL, field_name VARCHAR(50) DEFAULT NULL, ace_order SMALLINT UNSIGNED NOT NULL, mask INT NOT NULL, granting TINYINT(1) NOT NULL, granting_strategy VARCHAR(30) NOT NULL, audit_success TINYINT(1) NOT NULL, audit_failure TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4 (class_id, object_identity_id, field_name, ace_order), INDEX IDX_46C8B806EA000B103D9AB4A6DF9183C9 (class_id, object_identity_id, security_identity_id), INDEX IDX_46C8B806EA000B10 (class_id), INDEX IDX_46C8B8063D9AB4A6 (object_identity_id), INDEX IDX_46C8B806DF9183C9 (security_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAF8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA606F7D0E FOREIGN KEY (like_from) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAAC24F853 FOREIGN KEY (follower_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA3880B495 FOREIGN KEY (remix_root) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA63B7B817 FOREIGN KEY (remix_program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE gamejams_sampleprograms ADD CONSTRAINT FK_8EADA13654B8758D FOREIGN KEY (gamejam_id) REFERENCES GameJam (id)');
        $this->addSql('ALTER TABLE gamejams_sampleprograms ADD CONSTRAINT FK_8EADA1363EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE Notification ADD CONSTRAINT FK_A765AD328D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED222248A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED2222483EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED2222481B4D7895 FOREIGN KEY (user_id_rep) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id)');
        $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E812D5EB FOREIGN KEY (extension_id) REFERENCES extension (id)');
        $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E3EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E7140A621 FOREIGN KEY (rec_from_program_id) REFERENCES program (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE example ADD CONSTRAINT FK_6EEC9B9FFDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id)');
        $this->addSql('ALTER TABLE example ADD CONSTRAINT FK_6EEC9B9F3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE featured ADD CONSTRAINT FK_3C1359D4FDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id)');
        $this->addSql('ALTER TABLE featured ADD CONSTRAINT FK_3C1359D43EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE user_user ADD CONSTRAINT FK_F7129A803AD8644E FOREIGN KEY (user_source) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_user ADD CONSTRAINT FK_F7129A80233D34C1 FOREIGN KEY (user_target) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447FE54D947 FOREIGN KEY (group_id) REFERENCES fos_user_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2F3EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2FA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE mediapackagecategory_mediapackage ADD CONSTRAINT FK_3AA95277E74D4374 FOREIGN KEY (mediapackagecategory_id) REFERENCES media_package_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mediapackagecategory_mediapackage ADD CONSTRAINT FK_3AA952779CB0B96C FOREIGN KEY (mediapackage_id) REFERENCES media_package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media_package_file ADD CONSTRAINT FK_5E23F95412469DE2 FOREIGN KEY (category_id) REFERENCES media_package_category (id)');
        $this->addSql('ALTER TABLE mediapackagefile_flavor ADD CONSTRAINT FK_F139CC7D1F3493BC FOREIGN KEY (mediapackagefile_id) REFERENCES media_package_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mediapackagefile_flavor ADD CONSTRAINT FK_F139CC7DFDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED7784A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED77849D8F32D0 FOREIGN KEY (approved_by_user) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED778412469DE2 FOREIGN KEY (category_id) REFERENCES starter_category (id)');
        $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED778454B8758D FOREIGN KEY (gamejam_id) REFERENCES GameJam (id)');
        $this->addSql('ALTER TABLE program_tag ADD CONSTRAINT FK_88B68E093EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE program_tag ADD CONSTRAINT FK_88B68E09BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id)');
        $this->addSql('ALTER TABLE program_extension ADD CONSTRAINT FK_C985CCA83EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE program_extension ADD CONSTRAINT FK_C985CCA8812D5EB FOREIGN KEY (extension_id) REFERENCES extension (id)');
        $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A1748903F FOREIGN KEY (recommended_by_program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A7140A621 FOREIGN KEY (rec_from_program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE program_like ADD CONSTRAINT FK_A18515B43EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE program_like ADD CONSTRAINT FK_A18515B4A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE program_remix_backward_relation ADD CONSTRAINT FK_C294015B727ACA70 FOREIGN KEY (parent_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE program_remix_backward_relation ADD CONSTRAINT FK_C294015BDD62C21B FOREIGN KEY (child_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE program_remix_relation ADD CONSTRAINT FK_E5AD23B4C671CEA1 FOREIGN KEY (ancestor_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE program_remix_relation ADD CONSTRAINT FK_E5AD23B41844467D FOREIGN KEY (descendant_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE scratch_program_remix_relation ADD CONSTRAINT FK_3B275E756F212B35 FOREIGN KEY (catrobat_child_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66EF1A9D84 FOREIGN KEY (notification_id) REFERENCES CatroNotification (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66BB3368CF FOREIGN KEY (programId) REFERENCES program (id)');
        $this->addSql('ALTER TABLE user_like_similarity_relation ADD CONSTRAINT FK_132DCA08B4E2BF69 FOREIGN KEY (first_user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE user_like_similarity_relation ADD CONSTRAINT FK_132DCA08B02C53F8 FOREIGN KEY (second_user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE user_remix_similarity_relation ADD CONSTRAINT FK_143F09C7B4E2BF69 FOREIGN KEY (first_user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE user_remix_similarity_relation ADD CONSTRAINT FK_143F09C7B02C53F8 FOREIGN KEY (second_user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE acl_object_identities ADD CONSTRAINT FK_9407E54977FA751A FOREIGN KEY (parent_object_identity_id) REFERENCES acl_object_identities (id)');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors ADD CONSTRAINT FK_825DE2993D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors ADD CONSTRAINT FK_825DE299C671CEA1 FOREIGN KEY (ancestor_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B806EA000B10 FOREIGN KEY (class_id) REFERENCES acl_classes (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B8063D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B806DF9183C9 FOREIGN KEY (security_identity_id) REFERENCES acl_security_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66EF1A9D84');
        $this->addSql('ALTER TABLE gamejams_sampleprograms DROP FOREIGN KEY FK_8EADA13654B8758D');
        $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED778454B8758D');
        $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E812D5EB');
        $this->addSql('ALTER TABLE program_extension DROP FOREIGN KEY FK_C985CCA8812D5EB');
        $this->addSql('ALTER TABLE example DROP FOREIGN KEY FK_6EEC9B9FFDDA6450');
        $this->addSql('ALTER TABLE featured DROP FOREIGN KEY FK_3C1359D4FDDA6450');
        $this->addSql('ALTER TABLE mediapackagefile_flavor DROP FOREIGN KEY FK_F139CC7DFDDA6450');
        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA8D93D649');
        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA606F7D0E');
        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCAAC24F853');
        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA3880B495');
        $this->addSql('ALTER TABLE Notification DROP FOREIGN KEY FK_A765AD328D93D649');
        $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED222248A76ED395');
        $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED2222481B4D7895');
        $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6EA76ED395');
        $this->addSql('ALTER TABLE user_user DROP FOREIGN KEY FK_F7129A803AD8644E');
        $this->addSql('ALTER TABLE user_user DROP FOREIGN KEY FK_F7129A80233D34C1');
        $this->addSql('ALTER TABLE fos_user_user_group DROP FOREIGN KEY FK_B3C77447A76ED395');
        $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2FA76ED395');
        $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED7784A76ED395');
        $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED77849D8F32D0');
        $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556AA76ED395');
        $this->addSql('ALTER TABLE program_like DROP FOREIGN KEY FK_A18515B4A76ED395');
        $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66A76ED395');
        $this->addSql('ALTER TABLE user_like_similarity_relation DROP FOREIGN KEY FK_132DCA08B4E2BF69');
        $this->addSql('ALTER TABLE user_like_similarity_relation DROP FOREIGN KEY FK_132DCA08B02C53F8');
        $this->addSql('ALTER TABLE user_remix_similarity_relation DROP FOREIGN KEY FK_143F09C7B4E2BF69');
        $this->addSql('ALTER TABLE user_remix_similarity_relation DROP FOREIGN KEY FK_143F09C7B02C53F8');
        $this->addSql('ALTER TABLE fos_user_user_group DROP FOREIGN KEY FK_B3C77447FE54D947');
        $this->addSql('ALTER TABLE mediapackagecategory_mediapackage DROP FOREIGN KEY FK_3AA952779CB0B96C');
        $this->addSql('ALTER TABLE mediapackagecategory_mediapackage DROP FOREIGN KEY FK_3AA95277E74D4374');
        $this->addSql('ALTER TABLE media_package_file DROP FOREIGN KEY FK_5E23F95412469DE2');
        $this->addSql('ALTER TABLE mediapackagefile_flavor DROP FOREIGN KEY FK_F139CC7D1F3493BC');
        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA3EB8070A');
        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA63B7B817');
        $this->addSql('ALTER TABLE gamejams_sampleprograms DROP FOREIGN KEY FK_8EADA1363EB8070A');
        $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED2222483EB8070A');
        $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E3EB8070A');
        $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E7140A621');
        $this->addSql('ALTER TABLE example DROP FOREIGN KEY FK_6EEC9B9F3EB8070A');
        $this->addSql('ALTER TABLE featured DROP FOREIGN KEY FK_3C1359D43EB8070A');
        $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2F3EB8070A');
        $this->addSql('ALTER TABLE program_tag DROP FOREIGN KEY FK_88B68E093EB8070A');
        $this->addSql('ALTER TABLE program_extension DROP FOREIGN KEY FK_C985CCA83EB8070A');
        $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556A3EB8070A');
        $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556A1748903F');
        $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556A7140A621');
        $this->addSql('ALTER TABLE program_like DROP FOREIGN KEY FK_A18515B43EB8070A');
        $this->addSql('ALTER TABLE program_remix_backward_relation DROP FOREIGN KEY FK_C294015B727ACA70');
        $this->addSql('ALTER TABLE program_remix_backward_relation DROP FOREIGN KEY FK_C294015BDD62C21B');
        $this->addSql('ALTER TABLE program_remix_relation DROP FOREIGN KEY FK_E5AD23B4C671CEA1');
        $this->addSql('ALTER TABLE program_remix_relation DROP FOREIGN KEY FK_E5AD23B41844467D');
        $this->addSql('ALTER TABLE scratch_program_remix_relation DROP FOREIGN KEY FK_3B275E756F212B35');
        $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66BB3368CF');
        $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED778412469DE2');
        $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6EBAD26311');
        $this->addSql('ALTER TABLE program_tag DROP FOREIGN KEY FK_88B68E09BAD26311');
        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCAF8697D13');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B806EA000B10');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B806DF9183C9');
        $this->addSql('ALTER TABLE acl_object_identities DROP FOREIGN KEY FK_9407E54977FA751A');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE2993D9AB4A6');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE299C671CEA1');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B8063D9AB4A6');
        $this->addSql('DROP TABLE CatroNotification');
        $this->addSql('DROP TABLE GameJam');
        $this->addSql('DROP TABLE gamejams_sampleprograms');
        $this->addSql('DROP TABLE Notification');
        $this->addSql('DROP TABLE ProgramInappropriateReport');
        $this->addSql('DROP TABLE click_statistics');
        $this->addSql('DROP TABLE example');
        $this->addSql('DROP TABLE extension');
        $this->addSql('DROP TABLE featured');
        $this->addSql('DROP TABLE flavor');
        $this->addSql('DROP TABLE fos_user');
        $this->addSql('DROP TABLE user_user');
        $this->addSql('DROP TABLE fos_user_user_group');
        $this->addSql('DROP TABLE fos_user_group');
        $this->addSql('DROP TABLE homepage_click_statistics');
        $this->addSql('DROP TABLE media_package');
        $this->addSql('DROP TABLE media_package_category');
        $this->addSql('DROP TABLE mediapackagecategory_mediapackage');
        $this->addSql('DROP TABLE media_package_file');
        $this->addSql('DROP TABLE mediapackagefile_flavor');
        $this->addSql('DROP TABLE program');
        $this->addSql('DROP TABLE program_tag');
        $this->addSql('DROP TABLE program_extension');
        $this->addSql('DROP TABLE program_downloads');
        $this->addSql('DROP TABLE program_like');
        $this->addSql('DROP TABLE program_remix_backward_relation');
        $this->addSql('DROP TABLE program_remix_relation');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE rudewords');
        $this->addSql('DROP TABLE scratch_program');
        $this->addSql('DROP TABLE scratch_program_remix_relation');
        $this->addSql('DROP TABLE starter_category');
        $this->addSql('DROP TABLE survey');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE user_comment');
        $this->addSql('DROP TABLE user_like_similarity_relation');
        $this->addSql('DROP TABLE user_remix_similarity_relation');
        $this->addSql('DROP TABLE user_test_group');
        $this->addSql('DROP TABLE acl_classes');
        $this->addSql('DROP TABLE acl_security_identities');
        $this->addSql('DROP TABLE acl_object_identities');
        $this->addSql('DROP TABLE acl_object_identity_ancestors');
        $this->addSql('DROP TABLE acl_entries');
    }
}
