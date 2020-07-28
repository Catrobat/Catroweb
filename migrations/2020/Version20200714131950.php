<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200714131950 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fos_user ADD google_id VARCHAR(300) DEFAULT NULL, ADD facebook_id VARCHAR(300) DEFAULT NULL, ADD google_access_token VARCHAR(300) DEFAULT NULL, ADD facebook_access_token VARCHAR(300) DEFAULT NULL, ADD apple_id VARCHAR(300) DEFAULT NULL, ADD apple_access_token VARCHAR(300) DEFAULT NULL, ADD oauth_password_created TINYINT(1) DEFAULT \'0\' NOT NULL, ADD oauth_user TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fos_user DROP google_id, DROP facebook_id, DROP google_access_token, DROP facebook_access_token, DROP apple_id, DROP apple_access_token, DROP oauth_password_created, DROP oauth_user');
    }
}
