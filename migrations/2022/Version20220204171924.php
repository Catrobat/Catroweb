<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220204171924 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE Notification');
    $this->addSql('ALTER TABLE project DROP snapshots_enabled');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE Notification (id INT AUTO_INCREMENT NOT NULL, user CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', upload TINYINT(1) NOT NULL, report TINYINT(1) NOT NULL, summary TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_A765AD328D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    $this->addSql('ALTER TABLE Notification ADD CONSTRAINT FK_A765AD328D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE project ADD snapshots_enabled TINYINT(1) DEFAULT \'0\' NOT NULL');
  }
}
