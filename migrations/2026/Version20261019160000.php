<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019160000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add optional unique custom slug to projects (#6435)';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE program ADD slug VARCHAR(50) DEFAULT NULL');
    $this->addSql('CREATE UNIQUE INDEX UNIQ_92ED7784989D9B62 ON program (slug)');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX UNIQ_92ED7784989D9B62 ON program');
    $this->addSql('ALTER TABLE program DROP slug');
  }
}
