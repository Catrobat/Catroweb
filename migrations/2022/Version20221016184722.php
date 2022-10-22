<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221016184722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_AD5F9BFC451CDAD4 ON survey');
        $this->addSql('ALTER TABLE survey ADD flavor_id INT DEFAULT NULL, ADD platform VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFCFDDA6450 FOREIGN KEY (flavor_id) REFERENCES flavor (id)');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT survey_unique_constraint UNIQUE (language_code, flavor_id, platform)');
        $this->addSql('ALTER TABLE survey ADD constraint platforms_check_constraint CHECK (platform=\'ios\' or platform=\'android\')');
        $this->addSql('CREATE INDEX IDX_AD5F9BFCFDDA6450 ON survey (flavor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE survey DROP FOREIGN KEY FK_AD5F9BFCFDDA6450');
        $this->addSql('DROP INDEX survey_unique_constraint ON survey');
        $this->addSql('DROP INDEX platforms_check_constraint ON survey');
        $this->addSql('DROP INDEX IDX_AD5F9BFCFDDA6450 ON survey');
        $this->addSql('ALTER TABLE survey DROP flavor_id, DROP platform');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD5F9BFC451CDAD4 ON survey (language_code)');
    }
}
