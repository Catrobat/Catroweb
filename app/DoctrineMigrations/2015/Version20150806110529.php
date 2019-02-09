<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150806110529 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE media_package (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT NOT NULL, name_url LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_package_category (id INT AUTO_INCREMENT NOT NULL, package_id INT DEFAULT NULL, name LONGTEXT NOT NULL, INDEX IDX_A864559AF44CABFF (package_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_package_file (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name LONGTEXT NOT NULL, extension VARCHAR(255) NOT NULL, url LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, downloads INT NOT NULL, INDEX IDX_5E23F95412469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE media_package_category ADD CONSTRAINT FK_A864559AF44CABFF FOREIGN KEY (package_id) REFERENCES media_package (id)');
        $this->addSql('ALTER TABLE media_package_file ADD CONSTRAINT FK_5E23F95412469DE2 FOREIGN KEY (category_id) REFERENCES media_package_category (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE media_package_category DROP FOREIGN KEY FK_A864559AF44CABFF');
        $this->addSql('ALTER TABLE media_package_file DROP FOREIGN KEY FK_5E23F95412469DE2');
        $this->addSql('DROP TABLE media_package');
        $this->addSql('DROP TABLE media_package_category');
        $this->addSql('DROP TABLE media_package_file');
    }
}
