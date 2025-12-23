<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251223112851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fos_user DROP gplus_access_token, CHANGE google_access_token google_access_token LONGTEXT DEFAULT NULL, CHANGE facebook_access_token facebook_access_token LONGTEXT DEFAULT NULL, CHANGE apple_access_token apple_access_token LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fos_user ADD gplus_access_token VARCHAR(300) DEFAULT NULL, CHANGE google_access_token google_access_token VARCHAR(300) DEFAULT NULL, CHANGE facebook_access_token facebook_access_token VARCHAR(300) DEFAULT NULL, CHANGE apple_access_token apple_access_token VARCHAR(300) DEFAULT NULL');
    }
}
