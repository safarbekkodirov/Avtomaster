<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260619083004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE service_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, icon VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_FF3A42FC989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE master CHANGE rating rating NUMERIC(3, 1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE master_service ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE master_service ADD CONSTRAINT FK_3DBA77BA12469DE2 FOREIGN KEY (category_id) REFERENCES service_category (id)');
        $this->addSql('CREATE INDEX IDX_3DBA77BA12469DE2 ON master_service (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE service_category');
        $this->addSql('ALTER TABLE master CHANGE rating rating NUMERIC(3, 1) DEFAULT \'0.0\' NOT NULL');
        $this->addSql('ALTER TABLE master_service DROP FOREIGN KEY FK_3DBA77BA12469DE2');
        $this->addSql('DROP INDEX IDX_3DBA77BA12469DE2 ON master_service');
        $this->addSql('ALTER TABLE master_service DROP category_id');
    }
}
