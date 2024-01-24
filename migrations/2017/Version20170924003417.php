<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170924003417 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY FK_1D41556AA76ED395');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E3EB8070A');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E7140A621');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6EA76ED395');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E3EB8070A FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E7140A621 FOREIGN KEY (rec_from_project_id) REFERENCES project (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE ProjectInappropriateReport DROP FOREIGN KEY FK_ED2222483EB8070A');
    $this->addSql('ALTER TABLE ProjectInappropriateReport DROP FOREIGN KEY FK_ED222248A76ED395');
    $this->addSql('ALTER TABLE ProjectInappropriateReport ADD CONSTRAINT FK_ED2222483EB8070A FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE ProjectInappropriateReport ADD CONSTRAINT FK_ED222248A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2F3EB8070A');
    $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2FA76ED395');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2F3EB8070A FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2FA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE ProjectInappropriateReport DROP FOREIGN KEY FK_ED222248A76ED395');
    $this->addSql('ALTER TABLE ProjectInappropriateReport DROP FOREIGN KEY FK_ED2222483EB8070A');
    $this->addSql('ALTER TABLE ProjectInappropriateReport ADD CONSTRAINT FK_ED222248A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE ProjectInappropriateReport ADD CONSTRAINT FK_ED2222483EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E3EB8070A');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E7140A621');
    $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6EA76ED395');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E3EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E7140A621 FOREIGN KEY (rec_from_project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2F3EB8070A');
    $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2FA76ED395');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2F3EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2FA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE project_downloads DROP FOREIGN KEY FK_1D41556AA76ED395');
    $this->addSql('ALTER TABLE project_downloads ADD CONSTRAINT FK_1D41556AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
  }
}
