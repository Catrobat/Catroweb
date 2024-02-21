<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200617205116 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('ALTER TABLE project ADD scratch_id INT DEFAULT NULL');
    $this->addSql('CREATE UNIQUE INDEX UNIQ_92ED7784711DBBB4 ON project (scratch_id)');
    $this->addSql('ALTER TABLE fos_user ADD scratch_user_id INT DEFAULT NULL');
    $this->addSql('CREATE UNIQUE INDEX UNIQ_957A64797C85A057 ON fos_user (scratch_user_id)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('DROP INDEX UNIQ_957A64797C85A057 ON fos_user');
    $this->addSql('ALTER TABLE fos_user DROP scratch_user_id');
    $this->addSql('DROP INDEX UNIQ_92ED7784711DBBB4 ON project');
    $this->addSql('ALTER TABLE project DROP scratch_id');
  }
}
