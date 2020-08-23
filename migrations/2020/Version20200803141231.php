<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200803141231 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is not auto-generated
        $this->addSql('ALTER TABLE example ADD flavor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE example ADD CONSTRAINT FK_6EEC9B9FFDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id)');
        $this->addSql('CREATE INDEX IDX_6EEC9B9FFDDA6450 ON example (flavor_id)');
        $this->addSql('ALTER TABLE featured ADD flavor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE featured ADD CONSTRAINT FK_3C1359D4FDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id)');
        $this->addSql('CREATE INDEX IDX_3C1359D4FDDA6450 ON featured (flavor_id)');

        $this->addSql('UPDATE example, flavor
            SET example.flavor_id = flavor.id 
            WHERE example.flavor = flavor.name');
        $this->addSql('UPDATE example, flavor 
            SET example.flavor_id = (SELECT id from flavor WHERE name = \'pocketcode\') 
            WHERE NOT EXISTS(SELECT * FROM flavor WHERE flavor.name = example.flavor)');

        $this->addSql('UPDATE featured, flavor
            SET featured.flavor_id = flavor.id 
            WHERE featured.flavor = flavor.name');
        $this->addSql('UPDATE featured, flavor 
            SET featured.flavor_id = (SELECT id from flavor WHERE name = \'pocketcode\') 
            WHERE NOT EXISTS(SELECT * FROM flavor WHERE flavor.name = featured.flavor)');


        $this->addSql('ALTER TABLE example DROP flavor');
        $this->addSql('ALTER TABLE featured DROP flavor');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE example DROP FOREIGN KEY FK_6EEC9B9FFDDA6450');
        $this->addSql('DROP INDEX IDX_6EEC9B9FFDDA6450 ON example');
        $this->addSql('ALTER TABLE example ADD flavor VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'pocketcode\' NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP flavor_id');
        $this->addSql('ALTER TABLE featured DROP FOREIGN KEY FK_3C1359D4FDDA6450');
        $this->addSql('DROP INDEX IDX_3C1359D4FDDA6450 ON featured');
        $this->addSql('ALTER TABLE featured ADD flavor VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'pocketcode\' NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP flavor_id');
    }
}
