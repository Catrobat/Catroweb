<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211119152351 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE project_custom_translation DROP FOREIGN KEY FK_34070EC4166D1F9C');
    $this->addSql('ALTER TABLE project_custom_translation ADD CONSTRAINT FK_34070EC4166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE project_machine_translation DROP FOREIGN KEY FK_2FCF7039166D1F9C');
    $this->addSql('ALTER TABLE project_machine_translation ADD CONSTRAINT FK_2FCF7039166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_comment_machine_translation DROP FOREIGN KEY FK_2CEF8196F8697D13');
    $this->addSql('ALTER TABLE user_comment_machine_translation ADD CONSTRAINT FK_2CEF8196F8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id) ON DELETE CASCADE');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE project_custom_translation DROP FOREIGN KEY FK_34070EC4166D1F9C');
    $this->addSql('ALTER TABLE project_custom_translation ADD CONSTRAINT FK_34070EC4166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE project_machine_translation DROP FOREIGN KEY FK_2FCF7039166D1F9C');
    $this->addSql('ALTER TABLE project_machine_translation ADD CONSTRAINT FK_2FCF7039166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
    $this->addSql('ALTER TABLE user_comment_machine_translation DROP FOREIGN KEY FK_2CEF8196F8697D13');
    $this->addSql('ALTER TABLE user_comment_machine_translation ADD CONSTRAINT FK_2CEF8196F8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id)');
  }
}
