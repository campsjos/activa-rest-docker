<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220810180822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location DROP CONSTRAINT fk_5e9e89cb727aca70');
        $this->addSql('DROP INDEX idx_5e9e89cb727aca70');
        $this->addSql('ALTER TABLE location ADD province_id INT NOT NULL');
        $this->addSql('ALTER TABLE location ADD town_id INT NOT NULL');
        $this->addSql('ALTER TABLE location ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE location DROP parent_id');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBE946114A FOREIGN KEY (province_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB75E23604 FOREIGN KEY (town_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5E9E89CBE946114A ON location (province_id)');
        $this->addSql('CREATE INDEX IDX_5E9E89CB75E23604 ON location (town_id)');
        $this->addSql('ALTER TABLE property DROP CONSTRAINT fk_8bf21cde64d218e');
        $this->addSql('DROP INDEX idx_8bf21cde64d218e');
        $this->addSql('ALTER TABLE property DROP location_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE location DROP CONSTRAINT FK_5E9E89CBE946114A');
        $this->addSql('ALTER TABLE location DROP CONSTRAINT FK_5E9E89CB75E23604');
        $this->addSql('DROP INDEX IDX_5E9E89CBE946114A');
        $this->addSql('DROP INDEX IDX_5E9E89CB75E23604');
        $this->addSql('ALTER TABLE location ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE location DROP province_id');
        $this->addSql('ALTER TABLE location DROP town_id');
        $this->addSql('ALTER TABLE location DROP type');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT fk_5e9e89cb727aca70 FOREIGN KEY (parent_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5e9e89cb727aca70 ON location (parent_id)');
        $this->addSql('ALTER TABLE property ADD location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT fk_8bf21cde64d218e FOREIGN KEY (location_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_8bf21cde64d218e ON property (location_id)');
    }
}
