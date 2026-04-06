<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260404180000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add studio_id, comment_user_id, and project_user_id columns to CatroNotification for studio notifications';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE CatroNotification ADD studio_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    $this->addSql('ALTER TABLE CatroNotification ADD comment_user_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE CatroNotification ADD project_user_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_notify_studio FOREIGN KEY (studio_id) REFERENCES studio (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_notify_comment_user FOREIGN KEY (comment_user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_notify_project_user FOREIGN KEY (project_user_id) REFERENCES fos_user (id) ON DELETE SET NULL');
    $this->addSql('CREATE INDEX IDX_notify_studio ON CatroNotification (studio_id)');
    $this->addSql('CREATE INDEX IDX_notify_comment_user ON CatroNotification (comment_user_id)');
    $this->addSql('CREATE INDEX IDX_notify_project_user ON CatroNotification (project_user_id)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_notify_studio');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_notify_comment_user');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_notify_project_user');
    $this->addSql('DROP INDEX IDX_notify_studio ON CatroNotification');
    $this->addSql('DROP INDEX IDX_notify_comment_user ON CatroNotification');
    $this->addSql('DROP INDEX IDX_notify_project_user ON CatroNotification');
    $this->addSql('ALTER TABLE CatroNotification DROP studio_id');
    $this->addSql('ALTER TABLE CatroNotification DROP comment_user_id');
    $this->addSql('ALTER TABLE CatroNotification DROP project_user_id');
  }
}
