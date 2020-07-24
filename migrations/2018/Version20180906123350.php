<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180906123350 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE CatroNotification ADD follower_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAAC24F853 FOREIGN KEY (follower_id) REFERENCES fos_user (id)');
        $this->addSql('CREATE INDEX IDX_22087FCAAC24F853 ON CatroNotification (follower_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCAAC24F853');
        $this->addSql('DROP INDEX IDX_22087FCAAC24F853 ON CatroNotification');
        $this->addSql('ALTER TABLE CatroNotification DROP follower_id');
    }
}
