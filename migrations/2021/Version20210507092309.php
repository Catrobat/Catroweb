<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210507092309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE achievement (id INT AUTO_INCREMENT NOT NULL, internal_title VARCHAR(255) NOT NULL, ltm_code VARCHAR(255) NOT NULL, internal_description LONGTEXT NOT NULL, description_ltm_code VARCHAR(255) NOT NULL, badge_svg_path VARCHAR(255) NOT NULL, badge_locked_svg_path VARCHAR(255) NOT NULL, banner_svg_path VARCHAR(255) NOT NULL, banner_color VARCHAR(255) NOT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, priority INT NOT NULL, UNIQUE INDEX UNIQ_96737FF1276CE3C2 (internal_title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_achievement (id INT AUTO_INCREMENT NOT NULL, user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', achievement INT NOT NULL, unlocked_at DATETIME DEFAULT NULL, seen_at DATETIME DEFAULT NULL, INDEX IDX_3F68B6648D93D649 (user), INDEX IDX_3F68B66496737FF1 (achievement), UNIQUE INDEX user_achievement_unique (user, achievement), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_achievement ADD CONSTRAINT FK_3F68B6648D93D649 FOREIGN KEY (user) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_achievement ADD CONSTRAINT FK_3F68B66496737FF1 FOREIGN KEY (achievement) REFERENCES achievement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE CatroNotification DROP prize, DROP image_path');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_achievement DROP FOREIGN KEY FK_3F68B66496737FF1');
        $this->addSql('DROP TABLE achievement');
        $this->addSql('DROP TABLE user_achievement');
        $this->addSql('ALTER TABLE CatroNotification ADD prize LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD image_path LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
