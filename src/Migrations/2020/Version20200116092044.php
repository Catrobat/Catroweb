<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200116092044 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY IF EXISTS FK_CC794C66F1496545');
        $this->addSql('DROP INDEX IF EXISTS IDX_CC794C66F1496545 ON user_comment');
        $this->addSql('ALTER TABLE user_comment DROP IF EXISTS programs');
        $this->addSql('ALTER TABLE user_comment DROP COLUMN IF EXISTS programId');
        $this->addSql('ALTER TABLE user_comment ADD COLUMN IF NOT EXISTS programId CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66BB3368CF FOREIGN KEY (programId) REFERENCES program (id)');
        $this->addSql('CREATE INDEX IDX_CC794C66BB3368CF ON user_comment (programId)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY IF EXISTS FK_CC794C66BB3368CF');
        $this->addSql('DROP INDEX IF EXISTS IDX_CC794C66BB3368CF ON user_comment');
        $this->addSql('ALTER TABLE user_comment ADD programs CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', CHANGE programId programId INT NOT NULL');
        $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66F1496545 FOREIGN KEY (programs) REFERENCES program (id)');
        $this->addSql('CREATE INDEX IDX_CC794C66F1496545 ON user_comment (programs)');
    }
}
