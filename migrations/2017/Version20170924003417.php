<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170924003417 extends AbstractMigration
{
  /**
   * @param Schema $schema
   *
   * @throws \Doctrine\DBAL\DBALException
   */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556AA76ED395');
        $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E3EB8070A');
        $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E7140A621');
        $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6EA76ED395');
        $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E3EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E7140A621 FOREIGN KEY (rec_from_program_id) REFERENCES program (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED2222483EB8070A');
        $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED222248A76ED395');
        $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED2222483EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED222248A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2F3EB8070A');
        $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2FA76ED395');
        $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2F3EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2FA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    }

  /**
   * @param Schema $schema
   *
   * @throws \Doctrine\DBAL\DBALException
   */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED222248A76ED395');
        $this->addSql('ALTER TABLE ProgramInappropriateReport DROP FOREIGN KEY FK_ED2222483EB8070A');
        $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED222248A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE ProgramInappropriateReport ADD CONSTRAINT FK_ED2222483EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E3EB8070A');
        $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6E7140A621');
        $this->addSql('ALTER TABLE click_statistics DROP FOREIGN KEY FK_D9945A6EA76ED395');
        $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6E7140A621 FOREIGN KEY (rec_from_program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE click_statistics ADD CONSTRAINT FK_D9945A6EA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2F3EB8070A');
        $this->addSql('ALTER TABLE homepage_click_statistics DROP FOREIGN KEY FK_99AECB2FA76ED395');
        $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2F3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE homepage_click_statistics ADD CONSTRAINT FK_99AECB2FA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE program_downloads DROP FOREIGN KEY FK_1D41556AA76ED395');
        $this->addSql('ALTER TABLE program_downloads ADD CONSTRAINT FK_1D41556AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    }
}
