<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901232053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // First, update NULL values with default values
        $this->addSql("UPDATE users SET email = CONCAT('user_', id, '@example.com') WHERE email IS NULL OR email = ''");
        $this->addSql("UPDATE users SET first_name = 'Unknown' WHERE first_name IS NULL OR first_name = ''");
        $this->addSql("UPDATE users SET last_name = 'User' WHERE last_name IS NULL OR last_name = ''");
        
        // Then make the columns NOT NULL
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(255) NOT NULL, CHANGE first_name first_name VARCHAR(255) NOT NULL, CHANGE last_name last_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE first_name first_name VARCHAR(255) DEFAULT NULL, CHANGE last_name last_name VARCHAR(255) DEFAULT NULL');
    }
}
