<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160420162057 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE TABLE program_tag (program_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_88B68E093EB8070A (program_id), INDEX IDX_88B68E09BAD26311 (tag_id), PRIMARY KEY(program_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, en VARCHAR(255) DEFAULT NULL, de VARCHAR(255) DEFAULT NULL, it VARCHAR(255) DEFAULT NULL, fr VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE program_tag ADD CONSTRAINT FK_88B68E093EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE program_tag ADD CONSTRAINT FK_88B68E09BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id)');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE program_tag DROP FOREIGN KEY FK_88B68E09BAD26311');
    $this->addSql('DROP TABLE program_tag');
    $this->addSql('DROP TABLE tags');
  }
}
