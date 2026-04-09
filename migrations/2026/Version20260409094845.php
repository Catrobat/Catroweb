<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260409094845 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Add admin_user_id and join_request_action columns to CatroNotification for studio join request notifications.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE CatroNotification ADD join_request_action VARCHAR(20) DEFAULT NULL, ADD admin_user_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA6352511C FOREIGN KEY (admin_user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('CREATE INDEX IDX_22087FCA6352511C ON CatroNotification (admin_user_id)');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA6352511C');
    $this->addSql('DROP INDEX IDX_22087FCA6352511C ON CatroNotification');
    $this->addSql('ALTER TABLE CatroNotification DROP join_request_action, DROP admin_user_id');
  }
}
