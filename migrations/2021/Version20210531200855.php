<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210531200855 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE cronjob (name VARCHAR(255) NOT NULL, state VARCHAR(255) DEFAULT \'idle\' NOT NULL, cron_interval VARCHAR(255) DEFAULT \'1 days\' NOT NULL, priority INT DEFAULT 0 NOT NULL, start_at DATETIME DEFAULT NULL, end_at DATETIME DEFAULT NULL, result_code INT DEFAULT NULL, PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE cronjob');
  }
}
