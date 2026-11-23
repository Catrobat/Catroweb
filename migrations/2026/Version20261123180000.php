<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Only nullable columns: the release before this one keeps inserting comments and notifications without them.
 */
final class Version20261123180000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Comments on docs pages and the notification for the docs maintainers';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE user_comment ADD doc_path VARCHAR(191) DEFAULT NULL');
    $this->addSql('CREATE INDEX idx_comment_doc_date ON user_comment (doc_path, uploadDate)');
    $this->addSql('ALTER TABLE CatroNotification ADD doc_comment_id CHAR(36) DEFAULT NULL');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAC220F4E1 FOREIGN KEY (doc_comment_id) REFERENCES user_comment (id) ON DELETE CASCADE');
    $this->addSql('CREATE INDEX IDX_22087FCAC220F4E1 ON CatroNotification (doc_comment_id)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql("DELETE FROM CatroNotification WHERE notification_type = 'doc_comment'");
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCAC220F4E1');
    $this->addSql('DROP INDEX IDX_22087FCAC220F4E1 ON CatroNotification');
    $this->addSql('ALTER TABLE CatroNotification DROP doc_comment_id');
    $this->addSql('DELETE FROM user_comment WHERE doc_path IS NOT NULL');
    $this->addSql('DROP INDEX idx_comment_doc_date ON user_comment');
    $this->addSql('ALTER TABLE user_comment DROP doc_path');
  }
}
