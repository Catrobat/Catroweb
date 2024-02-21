<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170120184527 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE TABLE click_statistics (id INT AUTO_INCREMENT NOT NULL, tag_id INT DEFAULT NULL, extension_id INT DEFAULT NULL, project_id INT DEFAULT NULL, rec_from_project_id INT DEFAULT NULL, user_id INT DEFAULT NULL, type LONGTEXT NOT NULL, clicked_at DATETIME NOT NULL, ip LONGTEXT NOT NULL, latitude LONGTEXT DEFAULT NULL, longitude LONGTEXT DEFAULT NULL, country_code LONGTEXT DEFAULT NULL, country_name LONGTEXT DEFAULT NULL, street VARCHAR(255) DEFAULT \'\', postal_code VARCHAR(255) DEFAULT \'\', locality VARCHAR(255) DEFAULT \'\', user_agent VARCHAR(255) DEFAULT \'\', referrer VARCHAR(255) DEFAULT \'\', INDEX IDX_D9945A6EBAD26311 (tag_id), INDEX IDX_D9945A6E812D5EB (extension_id), INDEX IDX_D9945A6E3EB8070A (project_id), INDEX IDX_D9945A6E7140A621 (rec_from_project_id), INDEX IDX_D9945A6EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id)');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E812D5EB FOREIGN KEY (extension_id) REFERENCES extension (id)');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E3EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E7140A621 FOREIGN KEY (rec_from_project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE project_downloads ADD rec_from_project_id INT DEFAULT NULL');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556A7140A621 FOREIGN KEY (rec_from_project_id) REFERENCES project (id)');
    $this->addSql('CREATE INDEX IDX_1D41556A7140A621 ON project_downloads (rec_from_project_id)');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('DROP TABLE click_statistics');
    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY FK_1D41556A7140A621');
    $this->addSql('DROP INDEX IDX_1D41556A7140A621 ON project_downloads');
    $this->addSql('ALTER TABLE project_downloads DROP rec_from_project_id');
  }
}
