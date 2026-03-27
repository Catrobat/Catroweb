<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260327202316 extends AbstractMigration
{
  #[\Override]
  public function getDescription(): string
  {
    return 'Fix incorrect User indexes, add missing indexes for notifications/downloads/programs';
  }

  #[\Override]
  public function up(Schema $schema): void
  {
    // Fix User entity: facebook_id_idx and apple_id_idx incorrectly pointed to google_id
    $this->addSql('DROP INDEX apple_id_idx ON fos_user');
    $this->addSql('DROP INDEX facebook_id_idx ON fos_user');
    $this->addSql('CREATE INDEX apple_id_idx ON fos_user (apple_id)');
    $this->addSql('CREATE INDEX facebook_id_idx ON fos_user (facebook_id)');

    // Add missing notification indexes (queried on every page load for sidebar badge)
    $this->addSql('CREATE INDEX notif_user_seen_idx ON CatroNotification (user, seen)');
    $this->addSql('CREATE INDEX notif_user_id_idx ON CatroNotification (user, id)');

    // Add composite indexes for common program listing queries
    $this->addSql('CREATE INDEX program_listing_idx ON program (visible, auto_hidden, private, debug_build, uploaded_at)');
    $this->addSql('CREATE INDEX program_popularity_idx ON program (visible, auto_hidden, private, debug_build, popularity)');

    // Add missing program_downloads indexes
    $this->addSql('CREATE INDEX pd_downloaded_at_idx ON program_downloads (downloaded_at)');
    $this->addSql('ALTER TABLE program_downloads RENAME INDEX idx_1d41556a3eb8070a TO pd_program_idx');
    $this->addSql('ALTER TABLE program_downloads RENAME INDEX idx_1d41556a8d93d649 TO pd_user_idx');
  }

  #[\Override]
  public function down(Schema $schema): void
  {
    $this->addSql('DROP INDEX facebook_id_idx ON fos_user');
    $this->addSql('DROP INDEX apple_id_idx ON fos_user');
    $this->addSql('CREATE INDEX facebook_id_idx ON fos_user (google_id)');
    $this->addSql('CREATE INDEX apple_id_idx ON fos_user (google_id)');

    $this->addSql('DROP INDEX notif_user_seen_idx ON CatroNotification');
    $this->addSql('DROP INDEX notif_user_id_idx ON CatroNotification');

    $this->addSql('DROP INDEX program_listing_idx ON program');
    $this->addSql('DROP INDEX program_popularity_idx ON program');

    $this->addSql('DROP INDEX pd_downloaded_at_idx ON program_downloads');
    $this->addSql('ALTER TABLE program_downloads RENAME INDEX pd_program_idx TO IDX_1D41556A3EB8070A');
    $this->addSql('ALTER TABLE program_downloads RENAME INDEX pd_user_idx TO IDX_1D41556A8D93D649');
  }
}
