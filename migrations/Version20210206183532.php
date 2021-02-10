<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210206183532 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE ore_lavorate_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE ore_lavorate (id INT NOT NULL, azienda_id INT NOT NULL, cantiere_id INT NOT NULL, persona_id INT NOT NULL, causale_id INT NOT NULL, giorno TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, ore_pianificate VARCHAR(3) DEFAULT NULL, ore_lavorate VARCHAR(4) DEFAULT NULL, is_confirmed BOOLEAN DEFAULT \'false\' NOT NULL, key_reference VARCHAR(60) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F4C8377EADEA542 ON ore_lavorate (azienda_id)');
        $this->addSql('CREATE INDEX IDX_8F4C837770D2D4A4 ON ore_lavorate (cantiere_id)');
        $this->addSql('CREATE INDEX IDX_8F4C8377F5F88DB9 ON ore_lavorate (persona_id)');
        $this->addSql('CREATE INDEX IDX_8F4C8377A1E04A00 ON ore_lavorate (causale_id)');
        $this->addSql('ALTER TABLE ore_lavorate ADD CONSTRAINT FK_8F4C8377EADEA542 FOREIGN KEY (azienda_id) REFERENCES aziende (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ore_lavorate ADD CONSTRAINT FK_8F4C837770D2D4A4 FOREIGN KEY (cantiere_id) REFERENCES cantieri (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ore_lavorate ADD CONSTRAINT FK_8F4C8377F5F88DB9 FOREIGN KEY (persona_id) REFERENCES personale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ore_lavorate ADD CONSTRAINT FK_8F4C8377A1E04A00 FOREIGN KEY (causale_id) REFERENCES causali (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE ore_lavorate_id_seq CASCADE');
        $this->addSql('DROP TABLE ore_lavorate');
    }
}
