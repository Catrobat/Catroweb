<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170226222917 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE TABLE user_like_similarity_relation (first_user_id INT NOT NULL, second_user_id INT NOT NULL, similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_132DCA08B4E2BF69 (first_user_id), INDEX IDX_132DCA08B02C53F8 (second_user_id), PRIMARY KEY(first_user_id, second_user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE user_remix_similarity_relation (first_user_id INT NOT NULL, second_user_id INT NOT NULL, similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_143F09C7B4E2BF69 (first_user_id), INDEX IDX_143F09C7B02C53F8 (second_user_id), PRIMARY KEY(first_user_id, second_user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE homepage_click_statistics (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, user_id INT DEFAULT NULL, type LONGTEXT NOT NULL, clicked_at DATETIME NOT NULL, ip LONGTEXT NOT NULL, locale VARCHAR(255) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT \'\', referrer VARCHAR(255) DEFAULT \'\', INDEX IDX_99AECB2F3EB8070A (project_id), INDEX IDX_99AECB2FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE user_like_similarity_relation ADD CONSTRAINT FK_132DCA08B4E2BF69 FOREIGN KEY (first_user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE user_like_similarity_relation ADD CONSTRAINT FK_132DCA08B02C53F8 FOREIGN KEY (second_user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE user_remix_similarity_relation ADD CONSTRAINT FK_143F09C7B4E2BF69 FOREIGN KEY (first_user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE user_remix_similarity_relation ADD CONSTRAINT FK_143F09C7B02C53F8 FOREIGN KEY (second_user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2F3EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2FA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE project_downloads ADD user_specific_recommendation TINYINT(1) DEFAULT \'0\'');
    $this->addSql('ALTER TABLE click_statistics ADD user_specific_recommendation TINYINT(1) DEFAULT \'0\'');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('DROP TABLE user_like_similarity_relation');
    $this->addSql('DROP TABLE user_remix_similarity_relation');
    $this->addSql('DROP TABLE homepage_click_statistics');
    $this->addSql('ALTER TABLE click_statistics DROP user_specific_recommendation');
    $this->addSql('ALTER TABLE project_downloads DROP user_specific_recommendation');
  }
}
