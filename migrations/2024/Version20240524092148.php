<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240524092148 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE featured (id INT AUTO_INCREMENT NOT NULL, program_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', flavor_id INT DEFAULT NULL, imagetype VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, active TINYINT(1) NOT NULL, priority INT NOT NULL, for_ios TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_3C1359D43EB8070A (program_id), INDEX IDX_3C1359D4FDDA6450 (flavor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE program_downloads (id INT AUTO_INCREMENT NOT NULL, program_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', user CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', downloaded_at DATETIME NOT NULL, type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'project\' NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_1D41556A8D93D649 (user), INDEX IDX_1D41556A3EB8070A (program_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE program_remix_backward_relation (parent_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', child_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL, seen_at DATETIME DEFAULT NULL, INDEX IDX_C294015B727ACA70 (parent_id), INDEX IDX_C294015BDD62C21B (child_id), PRIMARY KEY(parent_id, child_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE achievement (id INT AUTO_INCREMENT NOT NULL, internal_title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, title_ltm_code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, internal_description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description_ltm_code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, badge_svg_path VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, badge_locked_svg_path VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, banner_svg_path VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, banner_color VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, enabled TINYINT(1) DEFAULT 1 NOT NULL, priority INT NOT NULL, UNIQUE INDEX UNIQ_96737FF1276CE3C2 (internal_title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE acl_object_identity_ancestors (object_identity_id INT UNSIGNED NOT NULL, ancestor_id INT UNSIGNED NOT NULL, INDEX IDX_825DE2993D9AB4A6 (object_identity_id), INDEX IDX_825DE299C671CEA1 (ancestor_id), PRIMARY KEY(object_identity_id, ancestor_id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE user_like_similarity_relation (first_user_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', second_user_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_132DCA08B4E2BF69 (first_user_id), INDEX IDX_132DCA08B02C53F8 (second_user_id), PRIMARY KEY(first_user_id, second_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE project_custom_translation (id INT AUTO_INCREMENT NOT NULL, project_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', language VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, credits LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_34070EC4166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE survey (id INT AUTO_INCREMENT NOT NULL, flavor_id INT DEFAULT NULL, language_code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, active TINYINT(1) DEFAULT 1 NOT NULL, platform VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_AD5F9BFCFDDA6450 (flavor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE FeatureFlag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, value TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE acl_classes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_type VARCHAR(200) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX UNIQ_69DD750638A36066 (class_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE media_package_category (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, priority INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE ProgramInappropriateReport (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', program_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', user_id_rep CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', note LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, time DATETIME NOT NULL, state INT NOT NULL, projectVersion INT NOT NULL, category TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_ED2222483EB8070A (program_id), INDEX IDX_ED2222481B4D7895 (user_id_rep), INDEX IDX_ED222248A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE scratch_program_remix_relation (scratch_parent_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', catrobat_child_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', INDEX IDX_3B275E756F212B35 (catrobat_child_id), PRIMARY KEY(scratch_parent_id, catrobat_child_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE mediapackagefile_flavor (mediapackagefile_id INT NOT NULL, flavor_id INT NOT NULL, INDEX IDX_F139CC7DFDDA6450 (flavor_id), INDEX IDX_F139CC7D1F3493BC (mediapackagefile_id), PRIMARY KEY(mediapackagefile_id, flavor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE media_package_file (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, extension VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, url LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, active TINYINT(1) NOT NULL, downloads INT NOT NULL, author VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_5E23F95412469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE user_user (user_source CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', user_target CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', INDEX IDX_F7129A803AD8644E (user_source), INDEX IDX_F7129A80233D34C1 (user_target), PRIMARY KEY(user_source, user_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE acl_object_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, parent_object_identity_id INT UNSIGNED DEFAULT NULL, class_id INT UNSIGNED NOT NULL, object_identifier VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, entries_inheriting TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_9407E5494B12AD6EA000B10 (object_identifier, class_id), INDEX IDX_9407E54977FA751A (parent_object_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE cronjob (name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, state VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'idle\' NOT NULL COLLATE `utf8mb4_unicode_ci`, cron_interval VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'1 days\' NOT NULL COLLATE `utf8mb4_unicode_ci`, priority INT DEFAULT 0 NOT NULL, start_at DATETIME DEFAULT NULL, end_at DATETIME DEFAULT NULL, result_code INT DEFAULT NULL, PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE studio_user (id INT AUTO_INCREMENT NOT NULL, studio CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', activity INT NOT NULL, user CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', role VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_on DATETIME DEFAULT NULL, created_on DATETIME NOT NULL, INDEX IDX_EC686DD18D93D649 (user), UNIQUE INDEX UNIQ_EC686DD1AC74095A (activity), INDEX IDX_EC686DD14A2B07B6 (studio), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE program (id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', user_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', approved_by_user CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', name VARCHAR(300) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, version INT DEFAULT 1 NOT NULL, views INT NOT NULL, downloads INT NOT NULL, uploaded_at DATETIME NOT NULL, last_modified_at DATETIME NOT NULL, language_version VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'0\' NOT NULL COLLATE `utf8mb4_unicode_ci`, catrobat_version_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, upload_ip VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, visible TINYINT(1) DEFAULT 1 NOT NULL, flavor VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'pocketcode\' NOT NULL COLLATE `utf8mb4_unicode_ci`, upload_language VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, filesize INT DEFAULT 0 NOT NULL, approved TINYINT(1) DEFAULT 0 NOT NULL, apk_status SMALLINT DEFAULT 0 NOT NULL, apk_request_time DATETIME DEFAULT NULL, apk_downloads INT DEFAULT 0 NOT NULL, private TINYINT(1) DEFAULT 0 NOT NULL, remix_root TINYINT(1) DEFAULT 1 NOT NULL, remix_migrated_at DATETIME DEFAULT NULL, debug_build TINYINT(1) DEFAULT 0 NOT NULL, credits LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, scratch_id INT DEFAULT NULL, rand INT DEFAULT 0 NOT NULL, popularity DOUBLE PRECISION DEFAULT \'0\' NOT NULL, not_for_kids INT DEFAULT 0 NOT NULL, INDEX language_version_idx (language_version), INDEX views_idx (views), INDEX IDX_92ED77849D8F32D0 (approved_by_user), INDEX flavor_idx (flavor), INDEX visible_idx (visible), INDEX downloads_idx (downloads), INDEX rand_idx (rand), INDEX user_idx (user_id), INDEX private_idx (private), INDEX name_idx (name), INDEX uploaded_at_idx (uploaded_at), UNIQUE INDEX UNIQ_92ED7784711DBBB4 (scratch_id), INDEX debug_build_idx (debug_build), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE acl_entries (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_id INT UNSIGNED NOT NULL, object_identity_id INT UNSIGNED DEFAULT NULL, security_identity_id INT UNSIGNED NOT NULL, field_name VARCHAR(50) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ace_order SMALLINT UNSIGNED NOT NULL, mask INT NOT NULL, granting TINYINT(1) NOT NULL, granting_strategy VARCHAR(30) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, audit_success TINYINT(1) NOT NULL, audit_failure TINYINT(1) NOT NULL, INDEX IDX_46C8B806DF9183C9 (security_identity_id), UNIQUE INDEX UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4 (class_id, object_identity_id, field_name, ace_order), INDEX IDX_46C8B806EA000B10 (class_id), INDEX IDX_46C8B806EA000B103D9AB4A6DF9183C9 (class_id, object_identity_id, security_identity_id), INDEX IDX_46C8B8063D9AB4A6 (object_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE example (id INT AUTO_INCREMENT NOT NULL, program_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', flavor_id INT DEFAULT NULL, imagetype VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, active TINYINT(1) NOT NULL, priority INT NOT NULL, for_ios TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_6EEC9B9F3EB8070A (program_id), INDEX IDX_6EEC9B9FFDDA6450 (flavor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE media_package (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, nameUrl LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE studio_join_requests (id INT AUTO_INCREMENT NOT NULL, user CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', studio CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', status VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_69E58A694A2B07B6 (studio), INDEX IDX_69E58A698D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE CatroNotification (id INT AUTO_INCREMENT NOT NULL, user CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', comment_id INT DEFAULT NULL, like_from CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', program_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', follower_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', remix_root CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', remix_program_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, message LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, notification_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, seen TINYINT(1) DEFAULT 0 NOT NULL, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_22087FCAAC24F853 (follower_id), INDEX IDX_22087FCAF8697D13 (comment_id), INDEX IDX_22087FCA63B7B817 (remix_program_id), INDEX IDX_22087FCA606F7D0E (like_from), INDEX IDX_22087FCA3880B495 (remix_root), INDEX IDX_22087FCA3EB8070A (program_id), INDEX IDX_22087FCA8D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE user_achievement (id INT AUTO_INCREMENT NOT NULL, user CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', achievement INT NOT NULL, unlocked_at DATETIME DEFAULT NULL, seen_at DATETIME DEFAULT NULL, INDEX IDX_3F68B66496737FF1 (achievement), UNIQUE INDEX user_achievement_unique (user, achievement), INDEX IDX_3F68B6648D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, internal_title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, title_ltm_code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, enabled TINYINT(1) DEFAULT 1 NOT NULL, INDEX internal_title_idx (internal_title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE studio (id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_public TINYINT(1) DEFAULT 1 NOT NULL, is_enabled TINYINT(1) DEFAULT 1 NOT NULL, allow_comments TINYINT(1) DEFAULT 1 NOT NULL, cover_path VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_on DATETIME DEFAULT NULL, created_on DATETIME NOT NULL, UNIQUE INDEX UNIQ_4A2B07B65E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE program_extension (program_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', extension_id INT NOT NULL, INDEX IDX_C985CCA83EB8070A (program_id), INDEX IDX_C985CCA8812D5EB (extension_id), PRIMARY KEY(program_id, extension_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE user_comment_machine_translation (id INT AUTO_INCREMENT NOT NULL, comment_id INT DEFAULT NULL, source_language VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, target_language VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, provider VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, usage_count INT NOT NULL, usage_per_month DOUBLE PRECISION NOT NULL, last_modified_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_2CEF8196F8697D13 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE mediapackagecategory_mediapackage (mediapackagecategory_id INT NOT NULL, mediapackage_id INT NOT NULL, INDEX IDX_3AA95277E74D4374 (mediapackagecategory_id), INDEX IDX_3AA952779CB0B96C (mediapackage_id), PRIMARY KEY(mediapackagecategory_id, mediapackage_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE studio_activity (id INT AUTO_INCREMENT NOT NULL, studio CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', user CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_on DATETIME NOT NULL, INDEX IDX_D076B8584A2B07B6 (studio), INDEX IDX_D076B8588D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE program_remix_relation (ancestor_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', descendant_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', depth INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, seen_at DATETIME DEFAULT NULL, INDEX IDX_E5AD23B41844467D (descendant_id), INDEX IDX_E5AD23B4C671CEA1 (ancestor_id), PRIMARY KEY(ancestor_id, descendant_id, depth)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE scratch_program (id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', name VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, username LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, last_modified_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE extension (id INT AUTO_INCREMENT NOT NULL, internal_title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, title_ltm_code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, enabled TINYINT(1) DEFAULT 1 NOT NULL, INDEX internal_title_idx (internal_title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE acl_security_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, identifier VARCHAR(200) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, username TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8835EE78772E836AF85E0677 (identifier, username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE project_machine_translation (id INT AUTO_INCREMENT NOT NULL, project_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', source_language VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, target_language VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, provider VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, usage_count INT NOT NULL, usage_per_month DOUBLE PRECISION NOT NULL, last_modified_at DATETIME NOT NULL, created_at DATETIME NOT NULL, cached_name VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, cached_description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, cached_credits LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_2FCF7039166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE fos_user (id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', username VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, username_canonical VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email_canonical VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, upload_token VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, avatar LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, gplus_access_token VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, gplus_id_token VARCHAR(5000) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, gplus_refresh_token VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, scratch_user_id INT DEFAULT NULL, google_id VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, facebook_id VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, google_access_token VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, facebook_access_token VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, apple_id VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, apple_access_token VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, oauth_password_created TINYINT(1) DEFAULT 0 NOT NULL, oauth_user TINYINT(1) DEFAULT 0 NOT NULL, verified TINYINT(1) DEFAULT 1 NOT NULL, about TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, currently_working_on VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ranking_score INT DEFAULT NULL, INDEX google_id_idx (google_id), INDEX username_canonical_idx (username_canonical), UNIQUE INDEX UNIQ_957A64797C85A057 (scratch_user_id), UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), INDEX facebook_id_idx (google_id), INDEX email_canonical_idx (email_canonical), INDEX upload_token_idx (upload_token), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), INDEX apple_id_idx (google_id), INDEX scratch_user_id_idx (scratch_user_id), INDEX confirmation_token_isx (confirmation_token), UNIQUE INDEX UNIQ_957A6479C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE program_like (program_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', user_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', type INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_A18515B4A76ED395 (user_id), INDEX IDX_A18515B43EB8070A (program_id), PRIMARY KEY(program_id, user_id, type)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE studio_program (id INT AUTO_INCREMENT NOT NULL, studio CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', activity INT NOT NULL, program CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', user CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', updated_on DATETIME DEFAULT NULL, created_on DATETIME NOT NULL, UNIQUE INDEX UNIQ_4CB3C24AAC74095A (activity), INDEX IDX_4CB3C24A4A2B07B6 (studio), INDEX IDX_4CB3C24A92ED7784 (program), INDEX IDX_4CB3C24A8D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE response_cache (id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, response_code INT NOT NULL, response LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, response_headers VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, cached_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE program_tag (program_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', tag_id INT NOT NULL, INDEX IDX_88B68E093EB8070A (program_id), INDEX IDX_88B68E09BAD26311 (tag_id), PRIMARY KEY(program_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', selector VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, hashedToken VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, requestedAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expiresAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE user_comment (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', studio CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', activity INT DEFAULT NULL, uploadDate DATETIME NOT NULL, text LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, isReported TINYINT(1) NOT NULL, programId CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', parent_id INT DEFAULT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, INDEX program_id_idx (programId), INDEX parent_id_idx (parent_id), INDEX studio_idx (studio), INDEX upload_date_idx (uploadDate), INDEX user_id_idx (user_id), UNIQUE INDEX UNIQ_CC794C66AC74095A (activity), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE maintanance_information (id INT AUTO_INCREMENT NOT NULL, internalTitle VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, icon VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ltmCode VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ltm_maintenanceStart DATE DEFAULT NULL, ltm_maintenanceEnd DATE DEFAULT NULL, ltm_additionalInformation LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE user_remix_similarity_relation (first_user_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', second_user_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_143F09C7B4E2BF69 (first_user_id), INDEX IDX_143F09C7B02C53F8 (second_user_id), PRIMARY KEY(first_user_id, second_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('CREATE TABLE flavor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_BC2534545E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE featured');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE program_downloads');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE program_remix_backward_relation');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE achievement');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE acl_object_identity_ancestors');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE user_like_similarity_relation');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE project_custom_translation');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE survey');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE FeatureFlag');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE acl_classes');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE media_package_category');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE ProgramInappropriateReport');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE scratch_program_remix_relation');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE refresh_tokens');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE mediapackagefile_flavor');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE media_package_file');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE user_user');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE acl_object_identities');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE cronjob');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE studio_user');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE program');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE acl_entries');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE example');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE media_package');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE studio_join_requests');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE CatroNotification');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE user_achievement');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE tags');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE studio');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE program_extension');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE user_comment_machine_translation');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE mediapackagecategory_mediapackage');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE studio_activity');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE program_remix_relation');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE scratch_program');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE extension');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE acl_security_identities');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE project_machine_translation');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE fos_user');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE program_like');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE studio_program');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE response_cache');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE program_tag');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE reset_password_request');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE user_comment');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE maintanance_information');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE user_remix_similarity_relation');
    $this->abortIf(
      !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
      "Migration can only be executed safely on '\\Doctrine\\DBAL\\Platforms\\MariaDBPlatform'."
    );

    $this->addSql('DROP TABLE flavor');
  }
}
