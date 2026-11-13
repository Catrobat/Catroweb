<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261113120000 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Notifications about a project are deleted with it: a RESTRICT key blocked the retention cleanup';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA3EB8070A');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA63B7B817');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA3EB8070A FOREIGN KEY (program_id) REFERENCES program (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA63B7B817 FOREIGN KEY (remix_program_id) REFERENCES program (id) ON DELETE CASCADE');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA3EB8070A');
    $this->addSql('ALTER TABLE CatroNotification DROP FOREIGN KEY FK_22087FCA63B7B817');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    $this->addSql('ALTER TABLE CatroNotification ADD CONSTRAINT FK_22087FCA63B7B817 FOREIGN KEY (remix_program_id) REFERENCES program (id)');
  }
}
