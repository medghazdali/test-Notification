<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901231731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_templates ADD plain_text_body_template LONGTEXT NOT NULL, ADD updated_at DATETIME NOT NULL, CHANGE body_template html_body_template LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE notification_attachments ADD created_at DATETIME NOT NULL, DROP size_bytes, CHANGE storage_path file_path VARCHAR(500) NOT NULL, CHANGE filename file_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE notifications CHANGE user_id user_id INT DEFAULT NULL, CHANGE recipient_email recipient_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD last_name VARCHAR(255) NOT NULL, ADD updated_at DATETIME NOT NULL, CHANGE name first_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD name VARCHAR(255) NOT NULL, DROP first_name, DROP last_name, DROP updated_at');
        $this->addSql('ALTER TABLE email_templates ADD body_template LONGTEXT NOT NULL, DROP html_body_template, DROP plain_text_body_template, DROP updated_at');
        $this->addSql('ALTER TABLE notifications CHANGE user_id user_id INT NOT NULL, CHANGE recipient_email recipient_email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE notification_attachments ADD size_bytes BIGINT NOT NULL, DROP created_at, CHANGE file_name filename VARCHAR(255) NOT NULL, CHANGE file_path storage_path VARCHAR(500) NOT NULL');
    }
}
