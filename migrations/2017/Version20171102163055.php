<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171102163055 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE user_comment ADD projects INT DEFAULT NULL');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66F1496545 FOREIGN KEY (projects) REFERENCES project (id)');
    $this->addSql('CREATE INDEX IDX_CC794C66F1496545 ON user_comment (projects)');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66F1496545');
    $this->addSql('DROP INDEX IDX_CC794C66F1496545 ON user_comment');
    $this->addSql('ALTER TABLE user_comment DROP projects');
  }
}
