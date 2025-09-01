<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901225700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE email_templates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, subject_template VARCHAR(500) NOT NULL, body_template LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notifications ADD email_template_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3131A730F FOREIGN KEY (email_template_id) REFERENCES email_templates (id)');
        $this->addSql('CREATE INDEX IDX_6000B0D3131A730F ON notifications (email_template_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3131A730F');
        $this->addSql('DROP TABLE email_templates');
        $this->addSql('DROP INDEX IDX_6000B0D3131A730F ON notifications');
        $this->addSql('ALTER TABLE notifications DROP email_template_id');
    }
}
