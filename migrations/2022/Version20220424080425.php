<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220424080425 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE fos_user_user_group DROP FOREIGN KEY FK_B3C77447FE54D947');
    $this->addSql('DROP TABLE fos_user_group');
    $this->addSql('DROP TABLE fos_user_user_group');
    $this->addSql('ALTER TABLE ProjectInappropriateReport CHANGE category category TEXT NOT NULL');
    $this->addSql('ALTER TABLE fos_user DROP date_of_birth, DROP firstname, DROP lastname, DROP website, DROP biography, DROP gender, DROP locale, DROP timezone, DROP phone, DROP facebook_uid, DROP facebook_name, DROP facebook_data, DROP twitter_uid, DROP twitter_name, DROP twitter_data, DROP gplus_uid, DROP gplus_name, DROP gplus_data, DROP token, DROP two_step_code');
    $this->addSql('ALTER TABLE studio_activity CHANGE type type ENUM(\'comment\', \'project\', \'user\')');
    $this->addSql('ALTER TABLE studio_user CHANGE role role ENUM(\'admin\', \'member\'), CHANGE status status ENUM(\'active\', \'banned\', \'pending_request\')');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE fos_user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_583D1F3E5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE fos_user_user_group (user_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', group_id INT NOT NULL, INDEX IDX_B3C77447A76ED395 (user_id), INDEX IDX_B3C77447FE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447FE54D947 FOREIGN KEY (group_id) REFERENCES fos_user_group (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE ProjectInappropriateReport CHANGE category category LONGTEXT NOT NULL');
    $this->addSql('ALTER TABLE fos_user ADD date_of_birth DATETIME DEFAULT NULL, ADD firstname VARCHAR(64) DEFAULT NULL, ADD lastname VARCHAR(64) DEFAULT NULL, ADD website VARCHAR(64) DEFAULT NULL, ADD biography VARCHAR(1000) DEFAULT NULL, ADD gender VARCHAR(1) DEFAULT NULL, ADD locale VARCHAR(8) DEFAULT NULL, ADD timezone VARCHAR(64) DEFAULT NULL, ADD phone VARCHAR(64) DEFAULT NULL, ADD facebook_uid VARCHAR(255) DEFAULT NULL, ADD facebook_name VARCHAR(255) DEFAULT NULL, ADD facebook_data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin` COMMENT \'(DC2Type:json)\', ADD twitter_uid VARCHAR(255) DEFAULT NULL, ADD twitter_name VARCHAR(255) DEFAULT NULL, ADD twitter_data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin` COMMENT \'(DC2Type:json)\', ADD gplus_uid VARCHAR(255) DEFAULT NULL, ADD gplus_name VARCHAR(255) DEFAULT NULL, ADD gplus_data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin` COMMENT \'(DC2Type:json)\', ADD token VARCHAR(255) DEFAULT NULL, ADD two_step_code VARCHAR(255) DEFAULT NULL');
    $this->addSql('ALTER TABLE studio_activity CHANGE type type VARCHAR(255) DEFAULT NULL');
    $this->addSql('ALTER TABLE studio_user CHANGE role role VARCHAR(255) DEFAULT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL');
  }
}
