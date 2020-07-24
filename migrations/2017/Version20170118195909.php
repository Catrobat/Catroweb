<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170118195909 extends AbstractMigration
{
  /**
   * @param Schema $schema
   *
   * @throws \Doctrine\DBAL\DBALException
   */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mediapackagecategory_mediapackage (mediapackagecategory_id INT NOT NULL, mediapackage_id INT NOT NULL, INDEX IDX_3AA95277E74D4374 (mediapackagecategory_id), INDEX IDX_3AA952779CB0B96C (mediapackage_id), PRIMARY KEY(mediapackagecategory_id, mediapackage_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mediapackagecategory_mediapackage ADD CONSTRAINT FK_3AA95277E74D4374 FOREIGN KEY (mediapackagecategory_id) REFERENCES media_package_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mediapackagecategory_mediapackage ADD CONSTRAINT FK_3AA952779CB0B96C FOREIGN KEY (mediapackage_id) REFERENCES media_package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media_package_category DROP FOREIGN KEY FK_A864559AF44CABFF');
        $this->addSql('DROP INDEX IDX_A864559AF44CABFF ON media_package_category');
        $this->addSql('ALTER TABLE media_package_category DROP package_id');
    }

  /**
   * @param Schema $schema
   *
   * @throws \Doctrine\DBAL\DBALException
   */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE mediapackagecategory_mediapackage');
        $this->addSql('ALTER TABLE media_package_category ADD package_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media_package_category ADD CONSTRAINT FK_A864559AF44CABFF FOREIGN KEY (package_id) REFERENCES media_package (id)');
        $this->addSql('CREATE INDEX IDX_A864559AF44CABFF ON media_package_category (package_id)');
    }
}
