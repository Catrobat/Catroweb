<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230217100800 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE INDEX internal_title_idx ON extension (internal_title)');
    $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_92ED7784A76ED395');
    $this->addSql('CREATE INDEX uploaded_at_idx ON project (uploaded_at)');
    $this->addSql('CREATE INDEX views_idx ON project (views)');
    $this->addSql('CREATE INDEX downloads_idx ON project (downloads)');
    $this->addSql('CREATE INDEX name_idx ON project (name)');
    $this->addSql('CREATE INDEX language_version_idx ON project (language_version)');
    $this->addSql('CREATE INDEX visible_idx ON project (visible)');
    $this->addSql('CREATE INDEX private_idx ON project (private)');
    $this->addSql('CREATE INDEX debug_build_idx ON project (debug_build)');
    $this->addSql('CREATE INDEX flavor_idx ON project (flavor)');
    $this->addSql('DROP INDEX idx_92ed7784a76ed395 ON project');
    $this->addSql('CREATE INDEX user_idx ON project (user_id)');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED7784A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('CREATE INDEX internal_title_idx ON tags (internal_title)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP INDEX uploaded_at_idx ON project');
    $this->addSql('DROP INDEX views_idx ON project');
    $this->addSql('DROP INDEX downloads_idx ON project');
    $this->addSql('DROP INDEX name_idx ON project');
    $this->addSql('DROP INDEX language_version_idx ON project');
    $this->addSql('DROP INDEX visible_idx ON project');
    $this->addSql('DROP INDEX private_idx ON project');
    $this->addSql('DROP INDEX debug_build_idx ON project');
    $this->addSql('DROP INDEX flavor_idx ON project');
    $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_92ED7784A76ED395');
    $this->addSql('DROP INDEX user_idx ON project');
    $this->addSql('CREATE INDEX IDX_92ED7784A76ED395 ON project (user_id)');
    $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_92ED7784A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('DROP INDEX internal_title_idx ON tags');
    $this->addSql('DROP INDEX internal_title_idx ON extension');
  }
}
