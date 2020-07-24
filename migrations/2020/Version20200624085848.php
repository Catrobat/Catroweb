<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200624085848 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is not auto generated
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE flavor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_BC2534545E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mediapackagefile_flavor (mediapackagefile_id INT NOT NULL, flavor_id INT NOT NULL, INDEX IDX_F139CC7D1F3493BC (mediapackagefile_id), INDEX IDX_F139CC7DFDDA6450 (flavor_id), PRIMARY KEY(mediapackagefile_id, flavor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mediapackagefile_flavor ADD CONSTRAINT FK_F139CC7D1F3493BC FOREIGN KEY (mediapackagefile_id) REFERENCES media_package_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mediapackagefile_flavor ADD CONSTRAINT FK_F139CC7DFDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO flavor(name) VALUES(\'pocketcode\'), (\'pocketalice\'), (\'pocketgalaxy\'), (\'phirocode\'), (\'luna\'), (\'create@school\'), (\'embroidery\'), (\'arduino\')');
        //Converts the old string flavors into the many_to_many relation. The first SELECT is for files with a valid flavor, the second SELECT adds the 'pocketcode' flavor to all files with invalid flavors(NULL, 'app')
        $this->addSql('INSERT IGNORE INTO mediapackagefile_flavor(mediapackagefile_id, flavor_id)
            SELECT f.id, flavor.id FROM flavor INNER JOIN media_package_file AS f ON f.flavor = flavor.name
            UNION
            SELECT f.id, (SELECT id from flavor WHERE name = \'pocketcode\') FROM media_package_file as f WHERE NOT EXISTS(SELECT * FROM flavor WHERE flavor.name = f.flavor)
        ');

        $this->addSql('ALTER TABLE media_package_file DROP flavor');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mediapackagefile_flavor DROP FOREIGN KEY FK_F139CC7DFDDA6450');
        $this->addSql('DROP TABLE flavor');
        $this->addSql('DROP TABLE mediapackagefile_flavor');
        $this->addSql('ALTER TABLE media_package_file ADD flavor VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'pocketcode\' COLLATE `utf8mb4_unicode_ci`');
    }
}
