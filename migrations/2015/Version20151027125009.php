<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151027125009 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE TABLE GameJam (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(300) NOT NULL, form_url VARCHAR(300) DEFAULT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE gamejams_sampleprojects (gamejam_id INT NOT NULL, project_id INT NOT NULL, INDEX IDX_8EADA13654B8758D (gamejam_id), INDEX IDX_8EADA1363EB8070A (project_id), PRIMARY KEY(gamejam_id, project_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE gamejams_sampleprojects ADD CONSTRAINT FK_8EADA13654B8758D FOREIGN KEY (gamejam_id) REFERENCES GameJam (id)');
    $this->addSql('ALTER TABLE gamejams_sampleprojects ADD CONSTRAINT FK_8EADA1363EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project ADD gamejam_id INT DEFAULT NULL, ADD gamejam_submission_accepted TINYINT(1) DEFAULT \'0\' NOT NULL, ADD gamejam_submission_date DATETIME DEFAULT NULL');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED778454B8758D FOREIGN KEY (gamejam_id) REFERENCES GameJam (id)');
    $this->addSql('CREATE INDEX IDX_92ED778454B8758D ON project (gamejam_id)');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_92ED778454B8758D');
    $this->addSql('ALTER TABLE gamejams_sampleprojects DROP FOREIGN KEY FK_8EADA13654B8758D');
    $this->addSql('DROP TABLE GameJam');
    $this->addSql('DROP TABLE gamejams_sampleprojects');
    $this->addSql('DROP INDEX IDX_92ED778454B8758D ON project');
    $this->addSql('ALTER TABLE project DROP gamejam_id, DROP gamejam_submission_accepted, DROP gamejam_submission_date');
  }
}
