<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231224235942 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE studio_join_requests (id INT AUTO_INCREMENT NOT NULL, user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', studio CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', status VARCHAR(20) NOT NULL, INDEX IDX_69E58A698D93D649 (user), INDEX IDX_69E58A694A2B07B6 (studio), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE studio_join_requests ADD CONSTRAINT FK_69E58A698D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_join_requests ADD CONSTRAINT FK_69E58A694A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_activity CHANGE type type ENUM(\'comment\', \'project\', \'user\')');
    $this->addSql('ALTER TABLE studio_user CHANGE role role ENUM(\'admin\', \'member\'), CHANGE status status ENUM(\'active\', \'banned\', \'pending_request\')');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE studio_join_requests DROP FOREIGN KEY FK_69E58A698D93D649');
    $this->addSql('ALTER TABLE studio_join_requests DROP FOREIGN KEY FK_69E58A694A2B07B6');
    $this->addSql('DROP TABLE studio_join_requests');
    $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL');
    $this->addSql('ALTER TABLE studio_activity CHANGE type type VARCHAR(255) DEFAULT NULL');
    $this->addSql('ALTER TABLE studio_user CHANGE role role VARCHAR(255) DEFAULT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL');
    $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL');
  }
}
