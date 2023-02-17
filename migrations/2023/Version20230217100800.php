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
    $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED7784A76ED395');
    $this->addSql('CREATE INDEX uploaded_at_idx ON program (uploaded_at)');
    $this->addSql('CREATE INDEX views_idx ON program (views)');
    $this->addSql('CREATE INDEX downloads_idx ON program (downloads)');
    $this->addSql('CREATE INDEX name_idx ON program (name)');
    $this->addSql('CREATE INDEX language_version_idx ON program (language_version)');
    $this->addSql('CREATE INDEX visible_idx ON program (visible)');
    $this->addSql('CREATE INDEX private_idx ON program (private)');
    $this->addSql('CREATE INDEX debug_build_idx ON program (debug_build)');
    $this->addSql('CREATE INDEX flavor_idx ON program (flavor)');
    $this->addSql('DROP INDEX idx_92ed7784a76ed395 ON program');
    $this->addSql('CREATE INDEX user_idx ON program (user_id)');
    $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED7784A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('CREATE INDEX internal_title_idx ON tags (internal_title)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP INDEX uploaded_at_idx ON program');
    $this->addSql('DROP INDEX views_idx ON program');
    $this->addSql('DROP INDEX downloads_idx ON program');
    $this->addSql('DROP INDEX name_idx ON program');
    $this->addSql('DROP INDEX language_version_idx ON program');
    $this->addSql('DROP INDEX visible_idx ON program');
    $this->addSql('DROP INDEX private_idx ON program');
    $this->addSql('DROP INDEX debug_build_idx ON program');
    $this->addSql('DROP INDEX flavor_idx ON program');
    $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED7784A76ED395');
    $this->addSql('DROP INDEX user_idx ON program');
    $this->addSql('CREATE INDEX IDX_92ED7784A76ED395 ON program (user_id)');
    $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED7784A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    $this->addSql('DROP INDEX internal_title_idx ON tags');
    $this->addSql('DROP INDEX internal_title_idx ON extension');
  }
}
