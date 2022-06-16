<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220320225726 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Add "about" and "currently working on" to user.';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE fos_user ADD about TEXT DEFAULT NULL, ADD currentlyWorkingOn VARCHAR(255) DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE fos_user DROP about, DROP currentlyWorkingOn');
  }
}
