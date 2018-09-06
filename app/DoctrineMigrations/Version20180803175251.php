<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180803175251 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE CatroNotification ADD like_from INT DEFAULT NULL, ADD program_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA606F7D0E FOREIGN KEY (like_from) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('CREATE INDEX IDX_22087FCA606F7D0E ON CatroNotification (like_from)');
        $this->addSql('CREATE INDEX IDX_22087FCA3EB8070A ON CatroNotification (program_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA606F7D0E');
        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA3EB8070A');
        $this->addSql('DROP INDEX IDX_22087FCA606F7D0E ON CatroNotification');
        $this->addSql('DROP INDEX IDX_22087FCA3EB8070A ON CatroNotification');
        $this->addSql('ALTER TABLE CatroNotification DROP like_from, DROP program_id');
    }
}
