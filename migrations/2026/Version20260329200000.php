<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260329200000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add date_of_birth, is_minor, and consent_status fields to fos_user for COPPA compliance';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user ADD date_of_birth DATE DEFAULT NULL');
    $this->addSql('ALTER TABLE fos_user ADD is_minor TINYINT(1) NOT NULL DEFAULT 0');
    $this->addSql("ALTER TABLE fos_user ADD consent_status VARCHAR(20) NOT NULL DEFAULT 'not_required'");
    $this->addSql('ALTER TABLE fos_user ADD parent_email VARCHAR(255) DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fos_user DROP date_of_birth');
    $this->addSql('ALTER TABLE fos_user DROP is_minor');
    $this->addSql('ALTER TABLE fos_user DROP consent_status');
    $this->addSql('ALTER TABLE fos_user DROP parent_email');
  }
}
