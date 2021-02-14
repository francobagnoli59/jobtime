<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210210172921 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE piano_ore_cantieri_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE piano_ore_cantieri (id INT NOT NULL, persona_id INT NOT NULL, cantiere_id INT NOT NULL, day_of_week SMALLINT NOT NULL, ore_previste VARCHAR(5) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, key_reference VARCHAR(40) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7A06F2F3D365D469 ON piano_ore_cantieri (key_reference)');
        $this->addSql('CREATE INDEX IDX_7A06F2F3F5F88DB9 ON piano_ore_cantieri (persona_id)');
        $this->addSql('CREATE INDEX IDX_7A06F2F370D2D4A4 ON piano_ore_cantieri (cantiere_id)');
        $this->addSql('ALTER TABLE piano_ore_cantieri ADD CONSTRAINT FK_7A06F2F3F5F88DB9 FOREIGN KEY (persona_id) REFERENCES personale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE piano_ore_cantieri ADD CONSTRAINT FK_7A06F2F370D2D4A4 FOREIGN KEY (cantiere_id) REFERENCES cantieri (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ore_lavorate ADD is_transfer BOOLEAN DEFAULT \'false\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE piano_ore_cantieri_id_seq CASCADE');
        $this->addSql('DROP TABLE piano_ore_cantieri');
        $this->addSql('ALTER TABLE ore_lavorate DROP is_transfer');
    }
}
