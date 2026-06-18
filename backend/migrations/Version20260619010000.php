<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add Review table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE review (
            id INT AUTO_INCREMENT NOT NULL,
            booking_id INT NOT NULL,
            master_id INT NOT NULL,
            client_id INT NOT NULL,
            rating SMALLINT NOT NULL,
            comment LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL,
            INDEX IDX_794381C6330196A (booking_id),
            INDEX IDX_794381C66C10804D (master_id),
            INDEX IDX_794381C19EB6921 (client_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6330196A FOREIGN KEY (booking_id) REFERENCES booking (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C66C10804D FOREIGN KEY (master_id) REFERENCES master (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6330196A');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C66C10804D');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C19EB6921');
        $this->addSql('DROP TABLE review');
    }
}
