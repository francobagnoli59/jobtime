<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210213153928 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE consolidati_cantieri_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE consolidati_personale_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE consolidati_cantieri (id INT NOT NULL, cantiere_id INT NOT NULL, mese_aziendale_id INT NOT NULL, key_reference VARCHAR(40) NOT NULL, ore_lavoro NUMERIC(7, 2) DEFAULT NULL, ore_straordinario NUMERIC(7, 2) DEFAULT NULL, ore_improduttive NUMERIC(7, 2) DEFAULT NULL, ore_ininfluenti NUMERIC(7, 2) DEFAULT NULL, ore_pianificate NUMERIC(7, 2) DEFAULT NULL, costo_ore_lavoro NUMERIC(10, 2) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_234BB870D365D469 ON consolidati_cantieri (key_reference)');
        $this->addSql('CREATE INDEX IDX_234BB87070D2D4A4 ON consolidati_cantieri (cantiere_id)');
        $this->addSql('CREATE INDEX IDX_234BB870FE7FA07B ON consolidati_cantieri (mese_aziendale_id)');
        $this->addSql('CREATE TABLE consolidati_personale (id INT NOT NULL, persona_id INT NOT NULL, mese_aziendale_id INT NOT NULL, key_reference VARCHAR(40) NOT NULL, ore_lavoro NUMERIC(6, 2) DEFAULT NULL, ore_straordinario NUMERIC(6, 2) DEFAULT NULL, ore_improduttive NUMERIC(6, 2) DEFAULT NULL, ore_ininfluenti NUMERIC(6, 2) DEFAULT NULL, ore_pianificate NUMERIC(6, 2) DEFAULT NULL, costo_lavoro NUMERIC(8, 2) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_28D66D5FD365D469 ON consolidati_personale (key_reference)');
        $this->addSql('CREATE INDEX IDX_28D66D5FF5F88DB9 ON consolidati_personale (persona_id)');
        $this->addSql('CREATE INDEX IDX_28D66D5FFE7FA07B ON consolidati_personale (mese_aziendale_id)');
        $this->addSql('ALTER TABLE consolidati_cantieri ADD CONSTRAINT FK_234BB87070D2D4A4 FOREIGN KEY (cantiere_id) REFERENCES cantieri (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE consolidati_cantieri ADD CONSTRAINT FK_234BB870FE7FA07B FOREIGN KEY (mese_aziendale_id) REFERENCES mesi_aziendali (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE consolidati_personale ADD CONSTRAINT FK_28D66D5FF5F88DB9 FOREIGN KEY (persona_id) REFERENCES personale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE consolidati_personale ADD CONSTRAINT FK_28D66D5FFE7FA07B FOREIGN KEY (mese_aziendale_id) REFERENCES mesi_aziendali (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cantieri ADD extra_rate NUMERIC(7, 2) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE mesi_aziendali ADD numero_persone SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE mesi_aziendali ADD numero_cantieri SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE ore_lavorate ALTER key_reference TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE personale ADD costo_straordinario NUMERIC(7, 2) DEFAULT \'0\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE consolidati_cantieri_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE consolidati_personale_id_seq CASCADE');
        $this->addSql('DROP TABLE consolidati_cantieri');
        $this->addSql('DROP TABLE consolidati_personale');
        $this->addSql('ALTER TABLE cantieri DROP extra_rate');
        $this->addSql('ALTER TABLE mesi_aziendali DROP numero_persone');
        $this->addSql('ALTER TABLE mesi_aziendali DROP numero_cantieri');
        $this->addSql('ALTER TABLE ore_lavorate ALTER key_reference TYPE VARCHAR(60)');
        $this->addSql('ALTER TABLE personale DROP costo_straordinario');
    }
}
