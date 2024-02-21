<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210730083426 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE studio (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, description TEXT NOT NULL, is_public TINYINT(1) DEFAULT \'1\' NOT NULL, is_enabled TINYINT(1) DEFAULT \'1\' NOT NULL, allow_comments TINYINT(1) DEFAULT \'1\' NOT NULL, cover_path VARCHAR(300) DEFAULT NULL, updated_on DATETIME DEFAULT NULL, created_on DATETIME NOT NULL, UNIQUE INDEX UNIQ_4A2B07B65E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE studio_activity (id INT AUTO_INCREMENT NOT NULL, studio CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', type ENUM(\'comment\', \'project\', \'user\'), created_on DATETIME NOT NULL, INDEX IDX_D076B8584A2B07B6 (studio), INDEX IDX_D076B8588D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE studio_project (id INT AUTO_INCREMENT NOT NULL, studio CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', activity INT NOT NULL, project CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', updated_on DATETIME DEFAULT NULL, created_on DATETIME NOT NULL, INDEX IDX_4CB3C24A4A2B07B6 (studio), UNIQUE INDEX UNIQ_4CB3C24AAC74095A (activity), INDEX IDX_4CB3C24A92ED7784 (project), INDEX IDX_4CB3C24A8D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE studio_user (id INT AUTO_INCREMENT NOT NULL, studio CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', activity INT NOT NULL, user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', role ENUM(\'admin\', \'member\'), status ENUM(\'active\', \'banned\', \'pending_request\'), updated_on DATETIME DEFAULT NULL, created_on DATETIME NOT NULL, INDEX IDX_EC686DD14A2B07B6 (studio), UNIQUE INDEX UNIQ_EC686DD1AC74095A (activity), INDEX IDX_EC686DD18D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE studio_activity ADD CONSTRAINT FK_D076B8584A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_activity ADD CONSTRAINT FK_D076B8588D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_project ADD CONSTRAINT FK_4CB3C24A4A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_project ADD CONSTRAINT FK_4CB3C24AAC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_project ADD CONSTRAINT FK_4CB3C24A92ED7784 FOREIGN KEY (project) REFERENCES project (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_project ADD CONSTRAINT FK_4CB3C24A8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_user ADD CONSTRAINT FK_EC686DD14A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_user ADD CONSTRAINT FK_EC686DD1AC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_user ADD CONSTRAINT FK_EC686DD18D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_comment ADD studio CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD activity INT DEFAULT NULL, ADD parent_id INT DEFAULT NULL');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C664A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66AC74095A FOREIGN KEY (activity) REFERENCES studio_activity (id) ON DELETE CASCADE');
    $this->addSql('CREATE INDEX IDX_CC794C664A2B07B6 ON user_comment (studio)');
    $this->addSql('CREATE UNIQUE INDEX UNIQ_CC794C66AC74095A ON user_comment (activity)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE studio_activity DROP FOREIGN KEY FK_D076B8584A2B07B6');
    $this->addSql('ALTER TABLE studio_project DROP FOREIGN KEY FK_4CB3C24A4A2B07B6');
    $this->addSql('ALTER TABLE studio_user DROP FOREIGN KEY FK_EC686DD14A2B07B6');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C664A2B07B6');
    $this->addSql('ALTER TABLE studio_project DROP FOREIGN KEY FK_4CB3C24AAC74095A');
    $this->addSql('ALTER TABLE studio_user DROP FOREIGN KEY FK_EC686DD1AC74095A');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66AC74095A');
    $this->addSql('DROP TABLE studio');
    $this->addSql('DROP TABLE studio_activity');
    $this->addSql('DROP TABLE studio_project');
    $this->addSql('DROP TABLE studio_user');
    $this->addSql('DROP INDEX IDX_CC794C664A2B07B6 ON user_comment');
    $this->addSql('DROP INDEX UNIQ_CC794C66AC74095A ON user_comment');
    $this->addSql('ALTER TABLE user_comment DROP studio, DROP activity, DROP parent_id');
  }
}
