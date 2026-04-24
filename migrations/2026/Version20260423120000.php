<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260423120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add composite indexes for cursor pagination and studio queries';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE INDEX idx_comment_project_date ON user_comment (programId, uploadDate)');
    $this->addSql('CREATE INDEX idx_comment_parent_date ON user_comment (parent_id, uploadDate)');
    $this->addSql('CREATE INDEX idx_activity_studio_type ON studio_activity (studio, type)');
    $this->addSql('CREATE INDEX idx_studio_user_status ON studio_user (studio, status)');
    $this->addSql('CREATE INDEX idx_join_request_studio_status ON studio_join_requests (studio, status)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX idx_comment_project_date ON user_comment');
    $this->addSql('DROP INDEX idx_comment_parent_date ON user_comment');
    $this->addSql('DROP INDEX idx_activity_studio_type ON studio_activity');
    $this->addSql('DROP INDEX idx_studio_user_status ON studio_user');
    $this->addSql('DROP INDEX idx_join_request_studio_status ON studio_join_requests');
  }
}
