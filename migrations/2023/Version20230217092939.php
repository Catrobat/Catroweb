<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230217092939 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE INDEX upload_token_idx ON fos_user (upload_token)');
    $this->addSql('CREATE INDEX confirmation_token_isx ON fos_user (confirmation_token)');
    $this->addSql('CREATE INDEX username_canonical_idx ON fos_user (username_canonical)');
    $this->addSql('CREATE INDEX email_canonical_idx ON fos_user (email_canonical)');
    $this->addSql('CREATE INDEX scratch_user_id_idx ON fos_user (scratch_user_id)');
    $this->addSql('CREATE INDEX google_id_idx ON fos_user (google_id)');
    $this->addSql('CREATE INDEX facebook_id_idx ON fos_user (google_id)');
    $this->addSql('CREATE INDEX apple_id_idx ON fos_user (google_id)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP INDEX upload_token_idx ON fos_user');
    $this->addSql('DROP INDEX confirmation_token_isx ON fos_user');
    $this->addSql('DROP INDEX username_canonical_idx ON fos_user');
    $this->addSql('DROP INDEX email_canonical_idx ON fos_user');
    $this->addSql('DROP INDEX scratch_user_id_idx ON fos_user');
    $this->addSql('DROP INDEX google_id_idx ON fos_user');
    $this->addSql('DROP INDEX facebook_id_idx ON fos_user');
    $this->addSql('DROP INDEX apple_id_idx ON fos_user');
  }
}
