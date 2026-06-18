<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add Booking table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE booking (
            id INT AUTO_INCREMENT NOT NULL,
            client_id INT NOT NULL,
            master_id INT NOT NULL,
            service_id INT NOT NULL,
            status VARCHAR(20) NOT NULL,
            total DECIMAL(10, 2) NOT NULL,
            slot_date DATE NOT NULL,
            slot_start_time TIME NOT NULL,
            slot_end_time TIME NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            cancelled_reason LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL,
            INDEX IDX_E000DEAB19EB6921 (client_id),
            INDEX IDX_E000DEAB6C10804D (master_id),
            INDEX IDX_E000DEABED5CA9E6 (service_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E000DEAB19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E000DEAB6C10804D FOREIGN KEY (master_id) REFERENCES master (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E000DEABED5CA9E6 FOREIGN KEY (service_id) REFERENCES master_service (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E000DEAB19EB6921');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E000DEAB6C10804D');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E000DEABED5CA9E6');
        $this->addSql('DROP TABLE booking');
    }
}
