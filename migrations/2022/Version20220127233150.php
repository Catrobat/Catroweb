<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220127233150 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'cached project machine translation';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE project_machine_translation ADD cached_name VARCHAR(300) DEFAULT NULL, ADD cached_description LONGTEXT DEFAULT NULL, ADD cached_credits LONGTEXT DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE project_machine_translation DROP cached_name, DROP cached_description, DROP cached_credits');
  }
}
