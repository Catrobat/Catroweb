<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200405160721 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('CREATE FULLTEXT INDEX IDX_9FB73D775E237E06 ON extension (name)');
    $this->addSql('CREATE FULLTEXT INDEX IDX_92ED7784BF3967505E237E066DE440264117D17E ON project (id, name, description, credits)');
    $this->addSql('CREATE FULLTEXT INDEX IDX_6FBC9426F359C1427D90298BA28E7734CC75CECE ON tags (en, de, it, fr)');
    $this->addSql('CREATE FULLTEXT INDEX IDX_957A6479F85E0677 ON fos_user (username)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

    $this->addSql('DROP INDEX IDX_9FB73D775E237E06 ON extension');
    $this->addSql('DROP INDEX IDX_957A6479F85E0677 ON fos_user');
    $this->addSql('DROP INDEX IDX_92ED7784BF3967505E237E066DE440264117D17E ON project');
    $this->addSql('DROP INDEX IDX_6FBC9426F359C1427D90298BA28E7734CC75CECE ON tags');
  }
}
