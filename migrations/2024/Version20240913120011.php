<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240913120011 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return '';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE CatroNotification CHANGE user user CHAR(36) NOT NULL, CHANGE like_from like_from CHAR(36) DEFAULT NULL, CHANGE program_id program_id CHAR(36) DEFAULT NULL, CHANGE follower_id follower_id CHAR(36) DEFAULT NULL, CHANGE remix_root remix_root CHAR(36) DEFAULT NULL, CHANGE remix_program_id remix_program_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE ProgramInappropriateReport CHANGE user_id user_id CHAR(36) DEFAULT NULL, CHANGE program_id program_id CHAR(36) DEFAULT NULL, CHANGE user_id_rep user_id_rep CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE example CHANGE program_id program_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE featured CHANGE program_id program_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE fos_user ADD verification_requested_at DATETIME DEFAULT NULL, CHANGE id id CHAR(36) NOT NULL, CHANGE roles roles JSON NOT NULL, CHANGE verified verified TINYINT(1) DEFAULT 0 NOT NULL');
    $this->addSql('ALTER TABLE user_user CHANGE user_source user_source CHAR(36) NOT NULL, CHANGE user_target user_target CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE program CHANGE id id CHAR(36) NOT NULL, CHANGE user_id user_id CHAR(36) NOT NULL, CHANGE approved_by_user approved_by_user CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE program_tag CHANGE program_id program_id CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE program_extension CHANGE program_id program_id CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE program_downloads CHANGE program_id program_id CHAR(36) DEFAULT NULL, CHANGE user user CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE program_like CHANGE program_id program_id CHAR(36) NOT NULL, CHANGE user_id user_id CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE program_remix_backward_relation CHANGE parent_id parent_id CHAR(36) NOT NULL, CHANGE child_id child_id CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE program_remix_relation CHANGE ancestor_id ancestor_id CHAR(36) NOT NULL, CHANGE descendant_id descendant_id CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE project_custom_translation CHANGE project_id project_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE project_machine_translation CHANGE project_id project_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE reset_password_request CHANGE user_id user_id CHAR(36) NOT NULL, CHANGE requestedAt requestedAt DATETIME NOT NULL, CHANGE expiresAt expiresAt DATETIME NOT NULL');
    $this->addSql('ALTER TABLE scratch_program CHANGE id id CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE scratch_program_remix_relation CHANGE scratch_parent_id scratch_parent_id CHAR(36) NOT NULL, CHANGE catrobat_child_id catrobat_child_id CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE studio CHANGE id id CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE studio_activity CHANGE studio studio CHAR(36) NOT NULL, CHANGE user user CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE studio_join_requests CHANGE user user CHAR(36) NOT NULL, CHANGE studio studio CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE studio_program CHANGE studio studio CHAR(36) NOT NULL, CHANGE program program CHAR(36) NOT NULL, CHANGE user user CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE studio_user CHANGE studio studio CHAR(36) NOT NULL, CHANGE user user CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE user_achievement CHANGE user user CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE user_comment CHANGE user_id user_id CHAR(36) DEFAULT NULL, CHANGE studio studio CHAR(36) DEFAULT NULL, CHANGE programId programId CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE first_user_id first_user_id CHAR(36) NOT NULL, CHANGE second_user_id second_user_id CHAR(36) NOT NULL');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE first_user_id first_user_id CHAR(36) NOT NULL, CHANGE second_user_id second_user_id CHAR(36) NOT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE featured CHANGE program_id program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_downloads CHANGE program_id program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE user user CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_remix_backward_relation CHANGE parent_id parent_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE child_id child_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE first_user_id first_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE second_user_id second_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project_custom_translation CHANGE project_id project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE ProgramInappropriateReport CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE user_id_rep user_id_rep CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE program_id program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE scratch_program_remix_relation CHANGE scratch_parent_id scratch_parent_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE catrobat_child_id catrobat_child_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_user CHANGE user_source user_source CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE user_target user_target CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE studio_user CHANGE studio studio CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE user user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE user_id user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE approved_by_user approved_by_user CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE example CHANGE program_id program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE studio_join_requests CHANGE user user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE studio studio CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE CatroNotification CHANGE user user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE like_from like_from CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE program_id program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE follower_id follower_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE remix_root remix_root CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE remix_program_id remix_program_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_achievement CHANGE user user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE studio CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_extension CHANGE program_id program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE studio_activity CHANGE studio studio CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE user user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_remix_relation CHANGE ancestor_id ancestor_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE descendant_id descendant_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE scratch_program CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE project_machine_translation CHANGE project_id project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE fos_user DROP verification_requested_at, CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE verified verified TINYINT(1) DEFAULT 1 NOT NULL, CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
    $this->addSql('ALTER TABLE program_like CHANGE program_id program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE user_id user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE studio_program CHANGE studio studio CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE program program CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE user user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE program_tag CHANGE program_id program_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE reset_password_request CHANGE requestedAt requestedAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE expiresAt expiresAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE user_id user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_comment CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE programId programId CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE studio studio CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE first_user_id first_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE second_user_id second_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
  }
}
