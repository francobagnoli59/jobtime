<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210226151836 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE aree_geografiche_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mansioni_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE aree_geografiche (id INT NOT NULL, area VARCHAR(60) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE mansioni (id INT NOT NULL, persone_id INT NOT NULL, mansione VARCHAR(40) NOT NULL, is_valid_da BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_453472267A9A1AEE ON mansioni (persone_id)');
        $this->addSql('ALTER TABLE mansioni ADD CONSTRAINT FK_453472267A9A1AEE FOREIGN KEY (persone_id) REFERENCES personale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personale ADD area_geografica_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD tipo_contratto VARCHAR(1) DEFAULT \'I\' NOT NULL');
        $this->addSql('ALTER TABLE personale ADD livello VARCHAR(5) DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD is_invalid BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE personale ADD is_partner BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->addSql('ALTER TABLE personale ADD scadenza_contratto DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD ultima_visita_medica DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD scadenza_visita_medica DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD is_reserved_visita BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD data_prevista_visita DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD note_visita VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD CONSTRAINT FK_5FEB70EE4AF09B2 FOREIGN KEY (area_geografica_id) REFERENCES aree_geografiche (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5FEB70EE4AF09B2 ON personale (area_geografica_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE personale DROP CONSTRAINT FK_5FEB70EE4AF09B2');
        $this->addSql('DROP SEQUENCE aree_geografiche_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mansioni_id_seq CASCADE');
        $this->addSql('DROP TABLE aree_geografiche');
        $this->addSql('DROP TABLE mansioni');
        $this->addSql('DROP INDEX IDX_5FEB70EE4AF09B2');
        $this->addSql('ALTER TABLE personale DROP area_geografica_id');
        $this->addSql('ALTER TABLE personale DROP tipo_contratto');
        $this->addSql('ALTER TABLE personale DROP livello');
        $this->addSql('ALTER TABLE personale DROP is_invalid');
        $this->addSql('ALTER TABLE personale DROP is_partner');
        $this->addSql('ALTER TABLE personale DROP scadenza_contratto');
        $this->addSql('ALTER TABLE personale DROP ultima_visita_medica');
        $this->addSql('ALTER TABLE personale DROP scadenza_visita_medica');
        $this->addSql('ALTER TABLE personale DROP is_reserved_visita');
        $this->addSql('ALTER TABLE personale DROP data_prevista_visita');
        $this->addSql('ALTER TABLE personale DROP note_visita');
    }
}
