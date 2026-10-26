<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261026120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Add program.scratch_converter_version: which converter made a converted Scratch project';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE program ADD scratch_converter_version SMALLINT DEFAULT NULL');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE program DROP scratch_converter_version');
  }
}
