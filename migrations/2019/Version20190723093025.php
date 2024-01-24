<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190723093025 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is not auto generated!
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    // deleting all user.id / project.id foreign keys to allow changing the type
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY IF EXISTS FK_22087FCAAC24F853');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY IF EXISTS FK_22087FCA606F7D0E');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY IF EXISTS FK_22087FCA8D93D649');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY IF EXISTS FK_22087FCA3EB8070A');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY IF EXISTS FK_D9945A6E3EB8070A');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY IF EXISTS FK_D9945A6E7140A621');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY IF EXISTS FK_D9945A6EA76ED395');
    $this->addSql('ALTER TABLE gamejams_sampleprojects DROP FOREIGN KEY IF EXISTS FK_8EADA1363EB8070A');
    $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY IF EXISTS FK_99AECB2F3EB8070A');
    $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY IF EXISTS FK_99AECB2FA76ED395');
    $this->addSql('ALTER TABLE featured DROP FOREIGN KEY IF EXISTS FK_3C1359D43EB8070A');
    $this->addSql('ALTER TABLE fos_user_user_group DROP FOREIGN KEY IF EXISTS FK_B3C77447A76ED395');
    $this->addSql('ALTER TABLE Notification DROP FOREIGN KEY IF EXISTS FK_A765AD328D93D649');
    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY IF EXISTS FK_1D41556A1748903F');
    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY IF EXISTS FK_1D41556A3EB8070A');
    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY IF EXISTS FK_1D41556A7140A621');
    $this->addSql('ALTER TABLE project_extension DROP FOREIGN KEY IF EXISTS FK_C985CCA83EB8070A');
    $this->addSql('ALTER TABLE ProjectInappropriateReport DROP FOREIGN KEY IF EXISTS FK_ED222248A76ED395');
    $this->addSql('ALTER TABLE ProjectInappropriateReport DROP FOREIGN KEY IF EXISTS FK_ED2222483EB8070A');
    $this->addSql('ALTER TABLE project DROP FOREIGN KEY IF EXISTS FK_92ED77849D8F32D0');
    $this->addSql('ALTER TABLE project DROP FOREIGN KEY IF EXISTS FK_92ED7784A76ED395');
    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY IF EXISTS FK_1D41556AA76ED395');
    $this->addSql('ALTER TABLE project_like DROP FOREIGN KEY IF EXISTS FK_A18515B43EB8070A');
    $this->addSql('ALTER TABLE project_like DROP FOREIGN KEY IF EXISTS FK_A18515B4A76ED395');
    $this->addSql('ALTER TABLE project_remix_relation DROP FOREIGN KEY IF EXISTS FK_E5AD23B4C671CEA1');
    $this->addSql('ALTER TABLE project_remix_relation DROP FOREIGN KEY IF EXISTS FK_E5AD23B41844467D');
    $this->addSql('ALTER TABLE project_remix_backward_relation DROP FOREIGN KEY IF EXISTS FK_C294015B727ACA70');
    $this->addSql('ALTER TABLE project_remix_backward_relation DROP FOREIGN KEY IF EXISTS FK_C294015BDD62C21B');
    $this->addSql('ALTER TABLE project_tag DROP FOREIGN KEY IF EXISTS FK_88B68E093EB8070A');
    $this->addSql('ALTER TABLE scratch_project_remix_relation DROP FOREIGN KEY IF EXISTS FK_3B275E756F212B35');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY IF EXISTS FK_CC794C66F1496545');
    $this->addSql('ALTER TABLE user_like_similarity_relation DROP FOREIGN KEY IF EXISTS FK_132DCA08B4E2BF69');
    $this->addSql('ALTER TABLE user_like_similarity_relation DROP FOREIGN KEY IF EXISTS FK_132DCA08B02C53F8');
    $this->addSql('ALTER TABLE user_remix_similarity_relation DROP FOREIGN KEY IF EXISTS FK_143F09C7B4E2BF69');
    $this->addSql('ALTER TABLE user_remix_similarity_relation DROP FOREIGN KEY IF EXISTS FK_143F09C7B02C53F8');
    $this->addSql('ALTER TABLE user_user DROP FOREIGN KEY IF EXISTS FK_F7129A803AD8644E');
    $this->addSql('ALTER TABLE user_user DROP FOREIGN KEY IF EXISTS FK_F7129A80233D34C1');
    $this->addSql('DROP TABLE IF EXISTS user_user');

    // changing all integer id's for user and project to char36 guid types.
    $this->addSql('ALTER TABLE CatroNotification CHANGE project_id project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE CatroNotification CHANGE user user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE like_from like_from CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE follower_id follower_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE click_statistics CHANGE project_id project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE rec_from_project_id rec_from_project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE click_statistics CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE featured CHANGE project_id project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE fos_user CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE fos_user_user_group CHANGE user_id user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE homepage_click_statistics CHANGE project_id project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE homepage_click_statistics CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE gamejams_sampleprojects CHANGE project_id project_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE Notification CHANGE user user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project CHANGE user_id user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE approved_by_user approved_by_user CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project_tag CHANGE project_id project_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project_downloads CHANGE project_id project_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE rec_from_project_id rec_from_project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE recommended_by_project_id recommended_by_project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project_downloads CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project_extension CHANGE project_id project_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE ProjectInappropriateReport CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE ProjectInappropriateReport CHANGE project_id project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project_like CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project_like CHANGE project_id project_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project_remix_relation CHANGE ancestor_id ancestor_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE descendant_id descendant_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project_remix_backward_relation CHANGE parent_id parent_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE child_id child_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE scratch_project_remix_relation CHANGE scratch_parent_id scratch_parent_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE catrobat_child_id catrobat_child_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_comment CHANGE projects projects CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE first_user_id first_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE second_user_id second_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE first_user_id first_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE second_user_id second_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');

    // auto generated
    $this->addSql('ALTER TABLE acl_classes CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
    $this->addSql('ALTER TABLE acl_security_identities CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
    $this->addSql('ALTER TABLE acl_object_identities CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
    $this->addSql('ALTER TABLE acl_entries CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');

    // recreating all foreign keys.
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA3EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA606F7D0E FOREIGN KEY (like_from) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAAC24F853 FOREIGN KEY (follower_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E3EB8070A FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E7140A621 FOREIGN KEY (rec_from_project_id) REFERENCES project (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE featured ADD CONSTRAINT FK_3C1359D43EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE gamejams_sampleprojects ADD CONSTRAINT FK_8EADA1363EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2FA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2F3EB8070A FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE Notification ADD CONSTRAINT FK_A765AD328D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED77849D8F32D0 FOREIGN KEY (approved_by_user) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED7784A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556A1748903F FOREIGN KEY (recommended_by_project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556A3EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556A7140A621 FOREIGN KEY (rec_from_project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_extension ADD CONSTRAINT FK_C985CCA83EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE ProjectInappropriateReport ADD CONSTRAINT FK_ED222248A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE ProjectInappropriateReport ADD CONSTRAINT FK_ED2222483EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_like ADD CONSTRAINT FK_A18515B4A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE project_like ADD CONSTRAINT FK_A18515B43EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_remix_backward_relation ADD CONSTRAINT FK_C294015B727ACA70 FOREIGN KEY (parent_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_remix_backward_relation ADD CONSTRAINT FK_C294015BDD62C21B FOREIGN KEY (child_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_remix_relation ADD CONSTRAINT FK_E5AD23B4C671CEA1 FOREIGN KEY (ancestor_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_remix_relation ADD CONSTRAINT FK_E5AD23B41844467D FOREIGN KEY (descendant_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_tag ADD CONSTRAINT FK_88B68E093EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE scratch_project_remix_relation ADD CONSTRAINT FK_3B275E756F212B35 FOREIGN KEY (catrobat_child_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66F1496545 FOREIGN KEY (projects) REFERENCES project (id)');
    $this->addSql('ALTER TABLE user_like_similarity_relation ADD CONSTRAINT FK_132DCA08B4E2BF69 FOREIGN KEY (first_user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE user_like_similarity_relation ADD CONSTRAINT FK_132DCA08B02C53F8 FOREIGN KEY (second_user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE user_remix_similarity_relation ADD CONSTRAINT FK_143F09C7B4E2BF69 FOREIGN KEY (first_user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE user_remix_similarity_relation ADD CONSTRAINT FK_143F09C7B02C53F8 FOREIGN KEY (second_user_id) REFERENCES fos_user (id)');
    $this->addSql('CREATE TABLE user_user (user_source CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user_target CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_F7129A803AD8644E (user_source), INDEX IDX_F7129A80233D34C1 (user_target), PRIMARY KEY(user_source, user_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE user_user ADD CONSTRAINT FK_F7129A803AD8644E FOREIGN KEY (user_source) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_user ADD CONSTRAINT FK_F7129A80233D34C1 FOREIGN KEY (user_target) REFERENCES fos_user (id) ON DELETE CASCADE');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
  }
}
