<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170205230019 extends AbstractMigration
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

        $this->addSql('CREATE TABLE program_like (program_id INT NOT NULL, user_id INT NOT NULL, type INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_A18515B43EB8070A (program_id), INDEX IDX_A18515B4A76ED395 (user_id), PRIMARY KEY(program_id, user_id, type)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE program_like ADD CONSTRAINT FK_A18515B43EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE program_like ADD CONSTRAINT FK_A18515B4A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
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

        $this->addSql('DROP TABLE program_like');
    }
}
