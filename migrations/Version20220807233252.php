<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220807233252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property ADD province_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property ADD town_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property ADD zone_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDEE946114A FOREIGN KEY (province_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE75E23604 FOREIGN KEY (town_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE9F2C3FAB FOREIGN KEY (zone_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8BF21CDEE946114A ON property (province_id)');
        $this->addSql('CREATE INDEX IDX_8BF21CDE75E23604 ON property (town_id)');
        $this->addSql('CREATE INDEX IDX_8BF21CDE9F2C3FAB ON property (zone_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE property DROP CONSTRAINT FK_8BF21CDEE946114A');
        $this->addSql('ALTER TABLE property DROP CONSTRAINT FK_8BF21CDE75E23604');
        $this->addSql('ALTER TABLE property DROP CONSTRAINT FK_8BF21CDE9F2C3FAB');
        $this->addSql('DROP INDEX IDX_8BF21CDEE946114A');
        $this->addSql('DROP INDEX IDX_8BF21CDE75E23604');
        $this->addSql('DROP INDEX IDX_8BF21CDE9F2C3FAB');
        $this->addSql('ALTER TABLE property DROP province_id');
        $this->addSql('ALTER TABLE property DROP town_id');
        $this->addSql('ALTER TABLE property DROP zone_id');
    }
}
