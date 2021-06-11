<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210610101136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE moduli_raccolta_ore_cantieri_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE raccolta_ore_persone_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE moduli_raccolta_ore_cantieri (id INT NOT NULL, cantiere_id INT DEFAULT NULL, raccolta_ore_persona_id INT DEFAULT NULL, ore_giornaliere TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_221E4F4170D2D4A4 ON moduli_raccolta_ore_cantieri (cantiere_id)');
        $this->addSql('CREATE INDEX IDX_221E4F419089AC18 ON moduli_raccolta_ore_cantieri (raccolta_ore_persona_id)');
        $this->addSql('COMMENT ON COLUMN moduli_raccolta_ore_cantieri.ore_giornaliere IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE raccolta_ore_persone (id INT NOT NULL, anno_id INT NOT NULL, persona_id INT NOT NULL, mese VARCHAR(2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, key_reference VARCHAR(30) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_252AF681D365D469 ON raccolta_ore_persone (key_reference)');
        $this->addSql('CREATE INDEX IDX_252AF6815E14C45E ON raccolta_ore_persone (anno_id)');
        $this->addSql('CREATE INDEX IDX_252AF681F5F88DB9 ON raccolta_ore_persone (persona_id)');
        $this->addSql('ALTER TABLE moduli_raccolta_ore_cantieri ADD CONSTRAINT FK_221E4F4170D2D4A4 FOREIGN KEY (cantiere_id) REFERENCES cantieri (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE moduli_raccolta_ore_cantieri ADD CONSTRAINT FK_221E4F419089AC18 FOREIGN KEY (raccolta_ore_persona_id) REFERENCES raccolta_ore_persone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE raccolta_ore_persone ADD CONSTRAINT FK_252AF6815E14C45E FOREIGN KEY (anno_id) REFERENCES festivita_annuali (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE raccolta_ore_persone ADD CONSTRAINT FK_252AF681F5F88DB9 FOREIGN KEY (persona_id) REFERENCES personale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE moduli_raccolta_ore_cantieri DROP CONSTRAINT FK_221E4F419089AC18');
        $this->addSql('DROP SEQUENCE moduli_raccolta_ore_cantieri_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE raccolta_ore_persone_id_seq CASCADE');
        $this->addSql('DROP TABLE moduli_raccolta_ore_cantieri');
        $this->addSql('DROP TABLE raccolta_ore_persone');
    }
}
