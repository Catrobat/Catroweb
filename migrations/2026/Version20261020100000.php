<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261020100000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add studio invite codes (#7397)';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE studio_invites (id CHAR(36) NOT NULL, code VARCHAR(16) NOT NULL, uses INT DEFAULT 0 NOT NULL, max_uses INT DEFAULT NULL, expires_at DATETIME DEFAULT NULL, created_on DATETIME NOT NULL, studio CHAR(36) NOT NULL, created_by CHAR(36) DEFAULT NULL, UNIQUE INDEX UNIQ_DD20DFA277153098 (code), INDEX IDX_DD20DFA24A2B07B6 (studio), INDEX IDX_DD20DFA2DE12AB56 (created_by), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('ALTER TABLE studio_invites ADD CONSTRAINT FK_DD20DFA24A2B07B6 FOREIGN KEY (studio) REFERENCES studio (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE studio_invites ADD CONSTRAINT FK_DD20DFA2DE12AB56 FOREIGN KEY (created_by) REFERENCES fos_user (id) ON DELETE SET NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE studio_invites DROP FOREIGN KEY FK_DD20DFA24A2B07B6');
    $this->addSql('ALTER TABLE studio_invites DROP FOREIGN KEY FK_DD20DFA2DE12AB56');
    $this->addSql('DROP TABLE studio_invites');
  }
}
