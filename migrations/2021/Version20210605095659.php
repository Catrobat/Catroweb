<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210605095659 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE gamejams_sampleprojects DROP FOREIGN KEY FK_8EADA13654B8758D');
    $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_92ED778454B8758D');
    $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_92ED778412469DE2');
    $this->addSql('DROP TABLE GameJam');
    $this->addSql('DROP TABLE gamejams_sampleprojects');
    $this->addSql('DROP TABLE rudewords');
    $this->addSql('DROP TABLE starter_category');
    $this->addSql('DROP INDEX IDX_92ED778454B8758D ON project');
    $this->addSql('DROP INDEX IDX_92ED778412469DE2 ON project');
    $this->addSql('ALTER TABLE project DROP category_id, DROP gamejam_id, DROP gamejam_submission_accepted, DROP gamejam_submission_date');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE GameJam (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(300) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, form_url VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, start DATETIME NOT NULL, end DATETIME NOT NULL, hashtag VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, flavor VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE gamejams_sampleprojects (gamejam_id INT NOT NULL, project_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', INDEX IDX_8EADA13654B8758D (gamejam_id), INDEX IDX_8EADA1363EB8070A (project_id), PRIMARY KEY(gamejam_id, project_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE rudewords (id INT AUTO_INCREMENT NOT NULL, word VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_4C737F87C3F17511 (word), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('CREATE TABLE starter_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, alias VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, order_pos INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('ALTER TABLE gamejams_sampleprojects ADD CONSTRAINT FK_8EADA1363EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE gamejams_sampleprojects ADD CONSTRAINT FK_8EADA13654B8758D FOREIGN KEY (gamejam_id) REFERENCES GameJam (id)');
    $this->addSql('ALTER TABLE project ADD category_id INT DEFAULT NULL, ADD gamejam_id INT DEFAULT NULL, ADD gamejam_submission_accepted TINYINT(1) DEFAULT \'0\' NOT NULL, ADD gamejam_submission_date DATETIME DEFAULT NULL');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED778412469DE2 FOREIGN KEY (category_id) REFERENCES starter_category (id)');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED778454B8758D FOREIGN KEY (gamejam_id) REFERENCES GameJam (id)');
    $this->addSql('CREATE INDEX IDX_92ED778454B8758D ON project (gamejam_id)');
    $this->addSql('CREATE INDEX IDX_92ED778412469DE2 ON project (category_id)');
  }
}
