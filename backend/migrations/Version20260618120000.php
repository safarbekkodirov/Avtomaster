<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260618120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add Master, MasterService, Region tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE region (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            lat DECIMAL(10, 7) DEFAULT NULL,
            lng DECIMAL(10, 7) DEFAULT NULL,
            UNIQUE INDEX UNIQ_F8156A34989D8B62 (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE master (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            bio LONGTEXT DEFAULT NULL,
            region_name VARCHAR(255) NOT NULL,
            address VARCHAR(255) DEFAULT NULL,
            lat DECIMAL(10, 7) DEFAULT NULL,
            lng DECIMAL(10, 7) DEFAULT NULL,
            rating DECIMAL(3, 1) DEFAULT \'0.0\' NOT NULL,
            reviews_count INT DEFAULT 0 NOT NULL,
            is_verified TINYINT(1) DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL,
            INDEX IDX_57B8F94BA76ED395 (user_id),
            UNIQUE INDEX UNIQ_57B8F94BA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE master_service (
            id INT AUTO_INCREMENT NOT NULL,
            master_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            duration_minutes INT NOT NULL,
            category_name VARCHAR(255) DEFAULT NULL,
            INDEX IDX_9C38CB34D110B222 (master_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE master ADD CONSTRAINT FK_57B8F94BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE master_service ADD CONSTRAINT FK_9C38CB34D110B222 FOREIGN KEY (master_id) REFERENCES master (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE master_service DROP FOREIGN KEY FK_9C38CB34D110B222');
        $this->addSql('ALTER TABLE master DROP FOREIGN KEY FK_57B8F94BA76ED395');
        $this->addSql('DROP TABLE master_service');
        $this->addSql('DROP TABLE master');
        $this->addSql('DROP TABLE region');
    }
}
