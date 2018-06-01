<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180525064111 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE program_downloads DROP latitude, DROP longitude, DROP street, DROP postal_code, DROP locality');
        $this->addSql('ALTER TABLE click_statistics DROP latitude, DROP longitude, DROP street, DROP postal_code, DROP locality');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE click_statistics ADD latitude LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD longitude LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD street VARCHAR(255) DEFAULT \'\' COLLATE utf8_unicode_ci, ADD postal_code VARCHAR(255) DEFAULT \'\' COLLATE utf8_unicode_ci, ADD locality VARCHAR(255) DEFAULT \'\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE program_downloads ADD latitude LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD longitude LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD street VARCHAR(255) DEFAULT \'\' COLLATE utf8_unicode_ci, ADD postal_code VARCHAR(255) DEFAULT \'\' COLLATE utf8_unicode_ci, ADD locality VARCHAR(255) DEFAULT \'\' COLLATE utf8_unicode_ci');
    }
}
