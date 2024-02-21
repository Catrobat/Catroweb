<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230303155205 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C664A2B07B6');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66BB3368CF');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66A76ED395');
    $this->addSql('CREATE INDEX parent_id_idx ON user_comment (parent_id)');
    $this->addSql('CREATE INDEX upload_date_idx ON user_comment (uploadDate)');
    $this->addSql('DROP INDEX idx_cc794c66a76ed395 ON user_comment');
    $this->addSql('CREATE INDEX user_id_idx ON user_comment (user_id)');
    $this->addSql('DROP INDEX idx_cc794c66bb3368cf ON user_comment');
    $this->addSql('CREATE INDEX project_id_idx ON user_comment (projectId)');
    $this->addSql('DROP INDEX idx_cc794c664a2b07b6 ON user_comment');
    $this->addSql('CREATE INDEX studio_idx ON user_comment (studio)');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C664A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66BB3368CF FOREIGN KEY (projectId) REFERENCES project (id)');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP INDEX parent_id_idx ON user_comment');
    $this->addSql('DROP INDEX upload_date_idx ON user_comment');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66A76ED395');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66BB3368CF');
    $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C664A2B07B6');
    $this->addSql('DROP INDEX studio_idx ON user_comment');
    $this->addSql('CREATE INDEX IDX_CC794C664A2B07B6 ON user_comment (studio)');
    $this->addSql('DROP INDEX project_id_idx ON user_comment');
    $this->addSql('CREATE INDEX IDX_CC794C66BB3368CF ON user_comment (projectId)');
    $this->addSql('DROP INDEX user_id_idx ON user_comment');
    $this->addSql('CREATE INDEX IDX_CC794C66A76ED395 ON user_comment (user_id)');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66BB3368CF FOREIGN KEY (projectId) REFERENCES project (id)');
    $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C664A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
  }
}
