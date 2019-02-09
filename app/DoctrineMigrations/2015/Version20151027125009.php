<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151027125009 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE GameJam (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(300) NOT NULL, form_url VARCHAR(300) DEFAULT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gamejams_sampleprograms (gamejam_id INT NOT NULL, program_id INT NOT NULL, INDEX IDX_8EADA13654B8758D (gamejam_id), INDEX IDX_8EADA1363EB8070A (program_id), PRIMARY KEY(gamejam_id, program_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE gamejams_sampleprograms ADD CONSTRAINT FK_8EADA13654B8758D FOREIGN KEY (gamejam_id) REFERENCES GameJam (id)');
        $this->addSql('ALTER TABLE gamejams_sampleprograms ADD CONSTRAINT FK_8EADA1363EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE program ADD gamejam_id INT DEFAULT NULL, ADD gamejam_submission_accepted TINYINT(1) DEFAULT \'0\' NOT NULL, ADD gamejam_submission_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED778454B8758D FOREIGN KEY (gamejam_id) REFERENCES GameJam (id)');
        $this->addSql('CREATE INDEX IDX_92ED778454B8758D ON program (gamejam_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED778454B8758D');
        $this->addSql('ALTER TABLE gamejams_sampleprograms DROP FOREIGN KEY FK_8EADA13654B8758D');
        $this->addSql('DROP TABLE GameJam');
        $this->addSql('DROP TABLE gamejams_sampleprograms');
        $this->addSql('DROP INDEX IDX_92ED778454B8758D ON program');
        $this->addSql('ALTER TABLE program DROP gamejam_id, DROP gamejam_submission_accepted, DROP gamejam_submission_date');
    }
}
