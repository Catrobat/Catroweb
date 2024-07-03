<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240607144703 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE acl_object_identity_ancestors');
    $this->addSql('DROP TABLE acl_classes');
    $this->addSql('DROP TABLE acl_object_identities');
    $this->addSql('DROP TABLE acl_entries');
    $this->addSql('DROP TABLE acl_security_identities');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAF8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA606F7D0E FOREIGN KEY (like_from) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAAC24F853 FOREIGN KEY (follower_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA3880B495 FOREIGN KEY (remix_root) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA63B7B817 FOREIGN KEY (remix_program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED222248A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED2222481B4D7895 FOREIGN KEY (user_id_rep) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED2222483EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE example ADD CONSTRAINT FK_6EEC9B9FFDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id)');
    $this->addSql('ALTER TABLE example ADD CONSTRAINT FK_6EEC9B9F3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE featured ADD CONSTRAINT FK_3C1359D4FDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id)');
    $this->addSql('ALTER TABLE featured ADD CONSTRAINT FK_3C1359D43EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE user_user ADD CONSTRAINT FK_F7129A803AD8644E FOREIGN KEY (user_source) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_user ADD CONSTRAINT FK_F7129A80233D34C1 FOREIGN KEY (user_target) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE mediapackagecategory_mediapackage ADD CONSTRAINT FK_3AA95277E74D4374 FOREIGN KEY (mediapackagecategory_id) REFERENCES media_package_category (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE mediapackagecategory_mediapackage ADD CONSTRAINT FK_3AA952779CB0B96C FOREIGN KEY (mediapackage_id) REFERENCES media_package (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE media_package_file ADD CONSTRAINT FK_5E23F95412469DE2 FOREIGN KEY (category_id) REFERENCES media_package_category (id)');
    $this->addSql('ALTER TABLE mediapackagefile_flavor ADD CONSTRAINT FK_F139CC7D1F3493BC FOREIGN KEY (mediapackagefile_id) REFERENCES media_package_file (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE mediapackagefile_flavor ADD CONSTRAINT FK_F139CC7DFDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE program CHANGE popularity popularity DOUBLE PRECISION DEFAULT \'0.0\' NOT NULL');
    $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED7784A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED77849D8F32D0 FOREIGN KEY (approved_by_user) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE program_tag ADD CONSTRAINT FK_88B68E093EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_tag ADD CONSTRAINT FK_88B68E09BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id)');
    $this->addSql('ALTER TABLE program_extension ADD CONSTRAINT FK_C985CCA83EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_extension ADD CONSTRAINT FK_C985CCA8812D5EB FOREIGN KEY (extension_id) REFERENCES extension (id)');
    $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A3EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE program_like ADD CONSTRAINT FK_A18515B43EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_like ADD CONSTRAINT FK_A18515B4A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE program_remix_backward_relation ADD CONSTRAINT FK_C294015B727ACA70 FOREIGN KEY (parent_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_remix_backward_relation ADD CONSTRAINT FK_C294015BDD62C21B FOREIGN KEY (child_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_remix_relation ADD CONSTRAINT FK_E5AD23B4C671CEA1 FOREIGN KEY (ancestor_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_remix_relation ADD CONSTRAINT FK_E5AD23B41844467D FOREIGN KEY (descendant_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE project_custom_translation ADD CONSTRAINT FK_34070EC4166D1F9C FOREIGN KEY (project_id) REFERENCES program (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE project_machine_translation ADD CONSTRAINT FK_2FCF7039166D1F9C FOREIGN KEY (project_id) REFERENCES program (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE scratch_program_remix_relation ADD CONSTRAINT FK_3B275E756F212B35 FOREIGN KEY (catrobat_child_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE studio_activity CHANGE type type ENUM(\'comment\', \'project\', \'user\')');
    $this->addSql('ALTER TABLE studio_activity ADD CONSTRAINT FK_D076B8584A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_activity ADD CONSTRAINT FK_D076B8588D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_join_requests ADD CONSTRAINT FK_69E58A698D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_join_requests ADD CONSTRAINT FK_69E58A694A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_program ADD CONSTRAINT FK_4CB3C24A4A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_program ADD CONSTRAINT FK_4CB3C24AAC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_program ADD CONSTRAINT FK_4CB3C24A92ED7784 FOREIGN KEY (program) REFERENCES program (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_program ADD CONSTRAINT FK_4CB3C24A8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_user CHANGE role role ENUM(\'admin\', \'member\'), CHANGE status status ENUM(\'active\', \'banned\', \'pending_request\')');
    $this->addSql('ALTER TABLE studio_user ADD CONSTRAINT FK_EC686DD14A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_user ADD CONSTRAINT FK_EC686DD1AC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_user ADD CONSTRAINT FK_EC686DD18D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFCFDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id)');
    $this->addSql('ALTER TABLE user_achievement ADD CONSTRAINT FK_3F68B6648D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_achievement ADD CONSTRAINT FK_3F68B66496737FF1 FOREIGN KEY (achievement) REFERENCES achievement (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66BB3368CF FOREIGN KEY (programId) REFERENCES program (id)');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C664A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66AC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_comment_machine_translation ADD CONSTRAINT FK_2CEF8196F8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.0\' NOT NULL');
    $this->addSql('ALTER TABLE user_like_similarity_relation ADD CONSTRAINT FK_132DCA08B4E2BF69 FOREIGN KEY (first_user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE user_like_similarity_relation ADD CONSTRAINT FK_132DCA08B02C53F8 FOREIGN KEY (second_user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.0\' NOT NULL');
    $this->addSql('ALTER TABLE user_remix_similarity_relation ADD CONSTRAINT FK_143F09C7B4E2BF69 FOREIGN KEY (first_user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE user_remix_similarity_relation ADD CONSTRAINT FK_143F09C7B02C53F8 FOREIGN KEY (second_user_id) REFERENCES fos_user (id)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE acl_object_identity_ancestors (object_identity_id INT UNSIGNED NOT NULL, ancestor_id INT UNSIGNED NOT NULL, INDEX IDX_825DE299C671CEA1 (ancestor_id), INDEX IDX_825DE2993D9AB4A6 (object_identity_id), PRIMARY KEY(object_identity_id, ancestor_id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE acl_classes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_type VARCHAR(200) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX UNIQ_69DD750638A36066 (class_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE acl_object_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, parent_object_identity_id INT UNSIGNED DEFAULT NULL, class_id INT UNSIGNED NOT NULL, object_identifier VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, entries_inheriting TINYINT(1) NOT NULL, INDEX IDX_9407E54977FA751A (parent_object_identity_id), UNIQUE INDEX UNIQ_9407E5494B12AD6EA000B10 (object_identifier, class_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE acl_entries (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_id INT UNSIGNED NOT NULL, object_identity_id INT UNSIGNED DEFAULT NULL, security_identity_id INT UNSIGNED NOT NULL, field_name VARCHAR(50) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ace_order SMALLINT UNSIGNED NOT NULL, mask INT NOT NULL, granting TINYINT(1) NOT NULL, granting_strategy VARCHAR(30) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, audit_success TINYINT(1) NOT NULL, audit_failure TINYINT(1) NOT NULL, INDEX IDX_46C8B806DF9183C9 (security_identity_id), INDEX IDX_46C8B806EA000B10 (class_id), INDEX IDX_46C8B8063D9AB4A6 (object_identity_id), INDEX IDX_46C8B806EA000B103D9AB4A6DF9183C9 (class_id, object_identity_id, security_identity_id), UNIQUE INDEX UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4 (class_id, object_identity_id, field_name, ace_order), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE acl_security_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, identifier VARCHAR(200) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, username TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8835EE78772E836AF85E0677 (identifier, username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('ALTER TABLE featured DROP FOREIGN KEY FK_3C1359D4FDDA6450');
    $this->addSql('ALTER TABLE featured DROP FOREIGN KEY FK_3C1359D43EB8070A');
    $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556A3EB8070A');
    $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556A8D93D649');
    $this->addSql('ALTER TABLE program_remix_backward_relation DROP FOREIGN KEY FK_C294015B727ACA70');
    $this->addSql('ALTER TABLE program_remix_backward_relation DROP FOREIGN KEY FK_C294015BDD62C21B');
    $this->addSql('ALTER TABLE user_like_similarity_relation DROP FOREIGN KEY FK_132DCA08B4E2BF69');
    $this->addSql('ALTER TABLE user_like_similarity_relation DROP FOREIGN KEY FK_132DCA08B02C53F8');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL');
    $this->addSql('ALTER TABLE project_custom_translation DROP FOREIGN KEY FK_34070EC4166D1F9C');
    $this->addSql('ALTER TABLE survey DROP FOREIGN KEY FK_AD5F9BFCFDDA6450');
    $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED222248A76ED395');
    $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED2222481B4D7895');
    $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED2222483EB8070A');
    $this->addSql('ALTER TABLE scratch_program_remix_relation DROP FOREIGN KEY FK_3B275E756F212B35');
    $this->addSql('ALTER TABLE mediapackagefile_flavor DROP FOREIGN KEY FK_F139CC7D1F3493BC');
    $this->addSql('ALTER TABLE mediapackagefile_flavor DROP FOREIGN KEY FK_F139CC7DFDDA6450');
    $this->addSql('ALTER TABLE media_package_file DROP FOREIGN KEY FK_5E23F95412469DE2');
    $this->addSql('ALTER TABLE user_user DROP FOREIGN KEY FK_F7129A803AD8644E');
    $this->addSql('ALTER TABLE user_user DROP FOREIGN KEY FK_F7129A80233D34C1');
    $this->addSql('ALTER TABLE studio_user DROP FOREIGN KEY FK_EC686DD14A2B07B6');
    $this->addSql('ALTER TABLE studio_user DROP FOREIGN KEY FK_EC686DD1AC74095A');
    $this->addSql('ALTER TABLE studio_user DROP FOREIGN KEY FK_EC686DD18D93D649');
    $this->addSql('ALTER TABLE studio_user CHANGE role role VARCHAR(255) DEFAULT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL');
    $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED7784A76ED395');
    $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED77849D8F32D0');
    $this->addSql('ALTER TABLE program CHANGE popularity popularity DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    $this->addSql('ALTER TABLE example DROP FOREIGN KEY FK_6EEC9B9FFDDA6450');
    $this->addSql('ALTER TABLE example DROP FOREIGN KEY FK_6EEC9B9F3EB8070A');
    $this->addSql('ALTER TABLE studio_join_requests DROP FOREIGN KEY FK_69E58A698D93D649');
    $this->addSql('ALTER TABLE studio_join_requests DROP FOREIGN KEY FK_69E58A694A2B07B6');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA8D93D649');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCAF8697D13');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA606F7D0E');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA3EB8070A');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCAAC24F853');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA3880B495');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA63B7B817');
    $this->addSql('ALTER TABLE user_achievement DROP FOREIGN KEY FK_3F68B6648D93D649');
    $this->addSql('ALTER TABLE user_achievement DROP FOREIGN KEY FK_3F68B66496737FF1');
    $this->addSql('ALTER TABLE program_extension DROP FOREIGN KEY FK_C985CCA83EB8070A');
    $this->addSql('ALTER TABLE program_extension DROP FOREIGN KEY FK_C985CCA8812D5EB');
    $this->addSql('ALTER TABLE user_comment_machine_translation DROP FOREIGN KEY FK_2CEF8196F8697D13');
    $this->addSql('ALTER TABLE mediapackagecategory_mediapackage DROP FOREIGN KEY FK_3AA95277E74D4374');
    $this->addSql('ALTER TABLE mediapackagecategory_mediapackage DROP FOREIGN KEY FK_3AA952779CB0B96C');
    $this->addSql('ALTER TABLE studio_activity DROP FOREIGN KEY FK_D076B8584A2B07B6');
    $this->addSql('ALTER TABLE studio_activity DROP FOREIGN KEY FK_D076B8588D93D649');
    $this->addSql('ALTER TABLE studio_activity CHANGE type type VARCHAR(255) DEFAULT NULL');
    $this->addSql('ALTER TABLE program_remix_relation DROP FOREIGN KEY FK_E5AD23B4C671CEA1');
    $this->addSql('ALTER TABLE program_remix_relation DROP FOREIGN KEY FK_E5AD23B41844467D');
    $this->addSql('ALTER TABLE project_machine_translation DROP FOREIGN KEY FK_2FCF7039166D1F9C');
    $this->addSql('ALTER TABLE program_like DROP FOREIGN KEY FK_A18515B43EB8070A');
    $this->addSql('ALTER TABLE program_like DROP FOREIGN KEY FK_A18515B4A76ED395');
    $this->addSql('ALTER TABLE studio_program DROP FOREIGN KEY FK_4CB3C24A4A2B07B6');
    $this->addSql('ALTER TABLE studio_program DROP FOREIGN KEY FK_4CB3C24AAC74095A');
    $this->addSql('ALTER TABLE studio_program DROP FOREIGN KEY FK_4CB3C24A92ED7784');
    $this->addSql('ALTER TABLE studio_program DROP FOREIGN KEY FK_4CB3C24A8D93D649');
    $this->addSql('ALTER TABLE program_tag DROP FOREIGN KEY FK_88B68E093EB8070A');
    $this->addSql('ALTER TABLE program_tag DROP FOREIGN KEY FK_88B68E09BAD26311');
    $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66A76ED395');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66BB3368CF');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C664A2B07B6');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66AC74095A');
    $this->addSql('ALTER TABLE user_remix_similarity_relation DROP FOREIGN KEY FK_143F09C7B4E2BF69');
    $this->addSql('ALTER TABLE user_remix_similarity_relation DROP FOREIGN KEY FK_143F09C7B02C53F8');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL');
  }
}
