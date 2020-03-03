<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200226130914 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C6664B64DCC');
        $this->addSql('DROP INDEX IDX_CC794C6664B64DCC ON user_comment');
        $this->addSql('ALTER TABLE user_comment ADD notification_id INT DEFAULT NULL, CHANGE userid user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66EF1A9D84 FOREIGN KEY (notification_id) REFERENCES CatroNotification (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_CC794C66A76ED395 ON user_comment (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CC794C66EF1A9D84 ON user_comment (notification_id)');
        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA976EECBE');
        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCAF8697D13');
        $this->addSql('DROP INDEX IDX_22087FCA976EECBE ON CatroNotification');
        $this->addSql('ALTER TABLE CatroNotification CHANGE remix_from remix_root CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA3880B495 FOREIGN KEY (remix_root) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAF8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_22087FCA3880B495 ON CatroNotification (remix_root)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA3880B495');
        $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCAF8697D13');
        $this->addSql('DROP INDEX IDX_22087FCA3880B495 ON CatroNotification');
        $this->addSql('ALTER TABLE CatroNotification CHANGE remix_root remix_from CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA976EECBE FOREIGN KEY (remix_from) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCAF8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id)');
        $this->addSql('CREATE INDEX IDX_22087FCA976EECBE ON CatroNotification (remix_from)');
        $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66A76ED395');
        $this->addSql('ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66EF1A9D84');
        $this->addSql('DROP INDEX IDX_CC794C66A76ED395 ON user_comment');
        $this->addSql('DROP INDEX UNIQ_CC794C66EF1A9D84 ON user_comment');
        $this->addSql('ALTER TABLE user_comment DROP notification_id, CHANGE user_id userId CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C6664B64DCC FOREIGN KEY (userId) REFERENCES fos_user (id)');
        $this->addSql('CREATE INDEX IDX_CC794C6664B64DCC ON user_comment (userId)');
    }
}
