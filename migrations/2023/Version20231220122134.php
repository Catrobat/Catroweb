<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231220122134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fos_user ADD ranking_score INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A64793F5D60E0 ON fos_user (ranking_score)');
        $this->addSql('ALTER TABLE studio_activity CHANGE type type ENUM(\'comment\', \'project\', \'user\')');
        $this->addSql('ALTER TABLE studio_user CHANGE role role ENUM(\'admin\', \'member\'), CHANGE status status ENUM(\'active\', \'banned\', \'pending_request\')');
        $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_like_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL');
        $this->addSql('ALTER TABLE user_remix_similarity_relation CHANGE similarity similarity NUMERIC(4, 3) DEFAULT \'0.000\' NOT NULL');
        $this->addSql('DROP INDEX UNIQ_957A64793F5D60E0 ON fos_user');
        $this->addSql('ALTER TABLE fos_user DROP ranking_score');
        $this->addSql('ALTER TABLE studio_user CHANGE role role VARCHAR(255) DEFAULT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE studio_activity CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
