<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161226072944 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE TABLE project_remix_relation (ancestor_id INT NOT NULL, descendant_id INT NOT NULL, depth INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, seen_at DATETIME DEFAULT NULL, INDEX IDX_E5AD23B4C671CEA1 (ancestor_id), INDEX IDX_E5AD23B41844467D (descendant_id), PRIMARY KEY(ancestor_id, descendant_id, depth)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE project_remix_backward_relation (parent_id INT NOT NULL, child_id INT NOT NULL, created_at DATETIME NOT NULL, seen_at DATETIME DEFAULT NULL, INDEX IDX_C294015B727ACA70 (parent_id), INDEX IDX_C294015BDD62C21B (child_id), PRIMARY KEY(parent_id, child_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE scratch_project_remix_relation (scratch_parent_id INT NOT NULL, catrobat_child_id INT NOT NULL, INDEX IDX_3B275E756F212B35 (catrobat_child_id), PRIMARY KEY(scratch_parent_id, catrobat_child_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE scratch_project (id INT NOT NULL, name VARCHAR(300) DEFAULT NULL, description LONGTEXT DEFAULT NULL, username LONGTEXT DEFAULT NULL, last_modified_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE project_remix_relation ADD CONSTRAINT FK_E5AD23B4C671CEA1 FOREIGN KEY (ancestor_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_remix_relation ADD CONSTRAINT FK_E5AD23B41844467D FOREIGN KEY (descendant_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_remix_backward_relation ADD CONSTRAINT FK_C294015B727ACA70 FOREIGN KEY (parent_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_remix_backward_relation ADD CONSTRAINT FK_C294015BDD62C21B FOREIGN KEY (child_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE scratch_project_remix_relation ADD CONSTRAINT FK_3B275E756F212B35 FOREIGN KEY (catrobat_child_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_92ED7784451AB72F');
    $this->addSql('DROP INDEX IDX_92ED7784451AB72F ON project');
    $this->addSql('ALTER TABLE project ADD remix_root TINYINT(1) DEFAULT \'1\' NOT NULL, ADD remix_migrated_at DATETIME DEFAULT NULL, DROP remix_id, DROP remix_count, CHANGE private private TINYINT(1) DEFAULT \'0\' NOT NULL');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('DROP TABLE project_remix_relation');
    $this->addSql('DROP TABLE project_remix_backward_relation');
    $this->addSql('DROP TABLE scratch_project_remix_relation');
    $this->addSql('DROP TABLE scratch_project');
    $this->addSql('ALTER TABLE project ADD remix_id INT DEFAULT NULL, ADD remix_count INT DEFAULT 0 NOT NULL, DROP remix_root, DROP remix_migrated_at, CHANGE private private TINYINT(1) DEFAULT \'0\'');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED7784451AB72F FOREIGN KEY (remix_id) REFERENCES project (id)');
    $this->addSql('CREATE INDEX IDX_92ED7784451AB72F ON project (remix_id)');
  }
}
