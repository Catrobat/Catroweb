<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210129204240 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE survey (id INT AUTO_INCREMENT NOT NULL, language_code VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, active TINYINT(1) DEFAULT \'1\' NOT NULL, UNIQUE INDEX UNIQ_AD5F9BFC451CDAD4 (language_code), UNIQUE INDEX UNIQ_AD5F9BFCF47645AE (url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE survey');
  }
}
