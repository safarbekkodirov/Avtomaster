<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619020000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add Payment table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE payment (
            id INT AUTO_INCREMENT NOT NULL,
            booking_id INT NOT NULL,
            status VARCHAR(20) NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            currency VARCHAR(3) NOT NULL,
            checkout_url VARCHAR(500) DEFAULT NULL,
            external_id VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL,
            INDEX IDX_6D424661330196A (booking_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D424661330196A FOREIGN KEY (booking_id) REFERENCES booking (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D424661330196A');
        $this->addSql('DROP TABLE payment');
    }
}
