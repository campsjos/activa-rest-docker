<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220812190534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property ADD situation_parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property ADD situation_child_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDEA9B38641 FOREIGN KEY (situation_parent_id) REFERENCES situation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDEAFE72531 FOREIGN KEY (situation_child_id) REFERENCES situation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8BF21CDEA9B38641 ON property (situation_parent_id)');
        $this->addSql('CREATE INDEX IDX_8BF21CDEAFE72531 ON property (situation_child_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE property DROP CONSTRAINT FK_8BF21CDEA9B38641');
        $this->addSql('ALTER TABLE property DROP CONSTRAINT FK_8BF21CDEAFE72531');
        $this->addSql('DROP INDEX IDX_8BF21CDEA9B38641');
        $this->addSql('DROP INDEX IDX_8BF21CDEAFE72531');
        $this->addSql('ALTER TABLE property DROP situation_parent_id');
        $this->addSql('ALTER TABLE property DROP situation_child_id');
    }
}
