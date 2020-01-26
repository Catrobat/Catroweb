<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200126204618 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE example');
        $this->addSql('DROP TABLE extensions');
        $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66BB3368CF');
        $this->addSql('DROP INDEX IDX_CC794C66BB3368CF ON user_comment');
        $this->addSql('ALTER TABLE user_comment CHANGE programid program CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C6692ED7784 FOREIGN KEY (program) REFERENCES program (id)');
        $this->addSql('CREATE INDEX IDX_CC794C6692ED7784 ON user_comment (program)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE example (id INT AUTO_INCREMENT NOT NULL, program_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', imagetype VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, active TINYINT(1) NOT NULL, flavor VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'pocketcode\' NOT NULL COLLATE `utf8mb4_unicode_ci`, priority INT NOT NULL, for_ios TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_6EEC9B9F3EB8070A (program_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE extensions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, prefix VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C6692ED7784');
        $this->addSql('DROP INDEX IDX_CC794C6692ED7784 ON user_comment');
        $this->addSql('ALTER TABLE user_comment CHANGE program programId CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66BB3368CF FOREIGN KEY (programId) REFERENCES program (id)');
        $this->addSql('CREATE INDEX IDX_CC794C66BB3368CF ON user_comment (programId)');
    }
}
