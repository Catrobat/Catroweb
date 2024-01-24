<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210202102935 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE ProjectInappropriateReport ADD user_id_rep CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE ProjectInappropriateReport ADD CONSTRAINT FK_ED2222481B4D7895 FOREIGN KEY (user_id_rep) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('CREATE INDEX IDX_ED2222481B4D7895 ON ProjectInappropriateReport (user_id_rep)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE ProjectInappropriateReport DROP FOREIGN KEY FK_ED2222481B4D7895');
    $this->addSql('DROP INDEX IDX_ED2222481B4D7895 ON ProjectInappropriateReport');
    $this->addSql('ALTER TABLE ProjectInappropriateReport DROP user_id_rep');
  }
}
