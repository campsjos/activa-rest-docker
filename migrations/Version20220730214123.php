<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220730214123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE feature_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE location_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE property_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE service_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE feature (id INT NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE location (id INT NOT NULL, parent_id INT DEFAULT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5E9E89CB727ACA70 ON location (parent_id)');
        $this->addSql('CREATE TABLE property (id INT NOT NULL, location_id INT DEFAULT NULL, category_id INT DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(255) DEFAULT NULL, latitude VARCHAR(255) DEFAULT NULL, longitude VARCHAR(255) DEFAULT NULL, featured BOOLEAN NOT NULL, image VARCHAR(255) DEFAULT NULL, gallery TEXT DEFAULT NULL, habitatsoft_id VARCHAR(255) NOT NULL, operation VARCHAR(255) NOT NULL, price VARCHAR(255) DEFAULT NULL, price_sqm VARCHAR(255) NOT NULL, reference VARCHAR(255) NOT NULL, area VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) NOT NULL, rooms INT DEFAULT NULL, baths INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8BF21CDE64D218E ON property (location_id)');
        $this->addSql('CREATE INDEX IDX_8BF21CDE12469DE2 ON property (category_id)');
        $this->addSql('COMMENT ON COLUMN property.gallery IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE property_feature (property_id INT NOT NULL, feature_id INT NOT NULL, PRIMARY KEY(property_id, feature_id))');
        $this->addSql('CREATE INDEX IDX_461A3F1E549213EC ON property_feature (property_id)');
        $this->addSql('CREATE INDEX IDX_461A3F1E60E4B879 ON property_feature (feature_id)');
        $this->addSql('CREATE TABLE property_service (property_id INT NOT NULL, service_id INT NOT NULL, PRIMARY KEY(property_id, service_id))');
        $this->addSql('CREATE INDEX IDX_B850D0AA549213EC ON property_service (property_id)');
        $this->addSql('CREATE INDEX IDX_B850D0AAED5CA9E6 ON property_service (service_id)');
        $this->addSql('CREATE TABLE service (id INT NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB727ACA70 FOREIGN KEY (parent_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE64D218E FOREIGN KEY (location_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property_feature ADD CONSTRAINT FK_461A3F1E549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property_feature ADD CONSTRAINT FK_461A3F1E60E4B879 FOREIGN KEY (feature_id) REFERENCES feature (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property_service ADD CONSTRAINT FK_B850D0AA549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property_service ADD CONSTRAINT FK_B850D0AAED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE property DROP CONSTRAINT FK_8BF21CDE12469DE2');
        $this->addSql('ALTER TABLE property_feature DROP CONSTRAINT FK_461A3F1E60E4B879');
        $this->addSql('ALTER TABLE location DROP CONSTRAINT FK_5E9E89CB727ACA70');
        $this->addSql('ALTER TABLE property DROP CONSTRAINT FK_8BF21CDE64D218E');
        $this->addSql('ALTER TABLE property_feature DROP CONSTRAINT FK_461A3F1E549213EC');
        $this->addSql('ALTER TABLE property_service DROP CONSTRAINT FK_B850D0AA549213EC');
        $this->addSql('ALTER TABLE property_service DROP CONSTRAINT FK_B850D0AAED5CA9E6');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE feature_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE location_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE property_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE service_id_seq CASCADE');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE feature');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE property');
        $this->addSql('DROP TABLE property_feature');
        $this->addSql('DROP TABLE property_service');
        $this->addSql('DROP TABLE service');
    }
}
