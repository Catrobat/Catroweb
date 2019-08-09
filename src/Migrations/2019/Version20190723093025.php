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
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

    // deleting all user.id / program.id foreign keys to allow changing the type
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCAAC24F853');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA606F7D0E');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA8D93D649');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA3EB8070A');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E3EB8070A');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E7140A621');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6EA76ED395');
    $this->addSql('ALTER TABLE gamejams_sampleprograms DROP FOREIGN KEY FK_8EADA1363EB8070A');
    $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2F3EB8070A');
    $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2FA76ED395');
    $this->addSql('ALTER TABLE featured DROP FOREIGN KEY FK_3C1359D43EB8070A');
    $this->addSql('ALTER TABLE fos_user_user_group DROP FOREIGN KEY FK_B3C77447A76ED395');
    $this->addSql('ALTER TABLE Notification DROP FOREIGN KEY FK_A765AD328D93D649');
    $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556A1748903F');
    $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556A3EB8070A');
    $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556A7140A621');
    $this->addSql('ALTER TABLE program_extension DROP FOREIGN KEY FK_C985CCA83EB8070A');
    $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED222248A76ED395');
    $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED2222483EB8070A');
    $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED77849D8F32D0');
    $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED7784A76ED395');
    $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556AA76ED395');
    $this->addSql('ALTER TABLE program_like DROP FOREIGN KEY FK_A18515B43EB8070A');
    $this->addSql('ALTER TABLE program_like DROP FOREIGN KEY FK_A18515B4A76ED395');
    $this->addSql('ALTER TABLE program_remix_relation DROP FOREIGN KEY FK_E5AD23B4C671CEA1');
    $this->addSql('ALTER TABLE program_remix_relation DROP FOREIGN KEY FK_E5AD23B41844467D');
    $this->addSql('ALTER TABLE program_remix_backward_relation DROP FOREIGN KEY FK_C294015B727ACA70');
    $this->addSql('ALTER TABLE program_remix_backward_relation DROP FOREIGN KEY FK_C294015BDD62C21B');
    $this->addSql('ALTER TABLE program_tag DROP FOREIGN KEY FK_88B68E093EB8070A');
    $this->addSql('ALTER TABLE scratch_program_remix_relation DROP FOREIGN KEY FK_3B275E756F212B35');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66F1496545');
    $this->addSql('ALTER TABLE user_like_similarity_relation DROP FOREIGN KEY FK_132DCA08B4E2BF69');
    $this->addSql('ALTER TABLE user_like_similarity_relation DROP FOREIGN KEY FK_132DCA08B02C53F8');
    $this->addSql('ALTER TABLE user_remix_similarity_relation DROP FOREIGN KEY FK_143F09C7B4E2BF69');
    $this->addSql('ALTER TABLE user_remix_similarity_relation DROP FOREIGN KEY FK_143F09C7B02C53F8');
    $this->addSql('ALTER TABLE user_user DROP FOREIGN KEY FK_F7129A803AD8644E');
    $this->addSql('ALTER TABLE user_user DROP FOREIGN KEY FK_F7129A80233D34C1');
    $this->addSql('DROP TABLE user_user');

    // changing all integer id's for user and program to char36 guid types.
    $this->addSql('ALTER TABLE CatroNotification CHANGE program_id program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE CatroNotification CHANGE user user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE like_from like_from CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE follower_id follower_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE click_statistics CHANGE program_id program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE rec_from_program_id rec_from_program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE click_statistics CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE featured CHANGE program_id program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE fos_user CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE fos_user_user_group CHANGE user_id user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE homepage_click_statistics CHANGE program_id program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE homepage_click_statistics CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE gamejams_sampleprograms CHANGE program_id program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE Notification CHANGE user user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program CHANGE user_id user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE approved_by_user approved_by_user CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_tag CHANGE program_id program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_downloads CHANGE program_id program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE rec_from_program_id rec_from_program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE recommended_by_program_id recommended_by_program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_downloads CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_extension CHANGE program_id program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE ProgramInappropriateReport CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE ProgramInappropriateReport CHANGE program_id program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_like CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_like CHANGE program_id program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_remix_relation CHANGE ancestor_id ancestor_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE descendant_id descendant_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_remix_backward_relation CHANGE parent_id parent_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE child_id child_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE scratch_program_remix_relation CHANGE scratch_parent_id scratch_parent_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE catrobat_child_id catrobat_child_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_comment CHANGE programs programs CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE first_user_id first_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE second_user_id second_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE first_user_id first_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE second_user_id second_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');

    // auto generated
    $this->addSql('ALTER TABLE acl_classes CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
    $this->addSql('ALTER TABLE acl_security_identities CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
    $this->addSql('ALTER TABLE acl_object_identities CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
    $this->addSql('ALTER TABLE acl_entries CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');

    // recreating all foreign keys.
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA606F7D0E FOREIGN KEY (like_from) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAAC24F853 FOREIGN KEY (follower_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E3EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E7140A621 FOREIGN KEY (rec_from_program_id) REFERENCES program (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE featured ADD CONSTRAINT FK_3C1359D43EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE gamejams_sampleprograms ADD CONSTRAINT FK_8EADA1363EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2FA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2F3EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE Notification ADD CONSTRAINT FK_A765AD328D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED77849D8F32D0 FOREIGN KEY (approved_by_user) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED7784A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A1748903F FOREIGN KEY (recommended_by_program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556A7140A621 FOREIGN KEY (rec_from_program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_extension ADD CONSTRAINT FK_C985CCA83EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED222248A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED2222483EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_like ADD CONSTRAINT FK_A18515B4A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE program_like ADD CONSTRAINT FK_A18515B43EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_remix_backward_relation ADD CONSTRAINT FK_C294015B727ACA70 FOREIGN KEY (parent_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_remix_backward_relation ADD CONSTRAINT FK_C294015BDD62C21B FOREIGN KEY (child_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_remix_relation ADD CONSTRAINT FK_E5AD23B4C671CEA1 FOREIGN KEY (ancestor_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_remix_relation ADD CONSTRAINT FK_E5AD23B41844467D FOREIGN KEY (descendant_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_tag ADD CONSTRAINT FK_88B68E093EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE scratch_program_remix_relation ADD CONSTRAINT FK_3B275E756F212B35 FOREIGN KEY (catrobat_child_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66F1496545 FOREIGN KEY (programs) REFERENCES program (id)');
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
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
  }
}
