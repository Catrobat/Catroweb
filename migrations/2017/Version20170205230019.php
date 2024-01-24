<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170205230019 extends AbstractMigration
{
  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE TABLE project_like (project_id INT NOT NULL, user_id INT NOT NULL, type INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_A18515B43EB8070A (project_id), INDEX IDX_A18515B4A76ED395 (user_id), PRIMARY KEY(project_id, user_id, type)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    $this->addSql('ALTER TABLE project_like ADD CONSTRAINT FK_A18515B43EB8070A FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_like ADD CONSTRAINT FK_A18515B4A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('DROP TABLE project_like');
  }
}
