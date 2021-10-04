<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170617183646 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\DBALException
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE TABLE CatroNotification (id INT AUTO_INCREMENT NOT NULL, user INT NOT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, notification_type VARCHAR(255) NOT NULL, prize LONGTEXT DEFAULT NULL, INDEX IDX_22087FCA8D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
  }

  /**
   * @throws \Doctrine\DBAL\DBALException
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('DROP TABLE CatroNotification');
  }
}
