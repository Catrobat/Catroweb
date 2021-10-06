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
   * @throws \Doctrine\DBAL\DBALException
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE user_comment ADD programs INT DEFAULT NULL');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66F1496545 FOREIGN KEY (programs) REFERENCES program (id)');
    $this->addSql('CREATE INDEX IDX_CC794C66F1496545 ON user_comment (programs)');
  }

  /**
   * @throws \Doctrine\DBAL\DBALException
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66F1496545');
    $this->addSql('DROP INDEX IDX_CC794C66F1496545 ON user_comment');
    $this->addSql('ALTER TABLE user_comment DROP programs');
  }
}
