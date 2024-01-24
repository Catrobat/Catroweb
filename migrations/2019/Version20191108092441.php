<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191108092441 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Credits hotfix';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE project ADD credits LONGTEXT NOT NULL');
    $this->addSql('ALTER TABLE user_test_group CHANGE user_id user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE project DROP credits');
    $this->addSql('ALTER TABLE user_test_group CHANGE user_id user_id INT NOT NULL');
  }
}
