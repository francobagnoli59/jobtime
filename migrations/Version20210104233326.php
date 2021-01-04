<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210104233326 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE cantieri_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE commenti_pubblici_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE province_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE regole_fatturazione_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cantieri (id INT NOT NULL, provincia_id INT NOT NULL, regola_fatturazione_id INT NOT NULL, name_job VARCHAR(255) NOT NULL, city VARCHAR(60) NOT NULL, is_public BOOLEAN NOT NULL, date_start_job DATE NOT NULL, date_end_job DATE NOT NULL, description_job TEXT DEFAULT NULL, maps VARCHAR(50) DEFAULT NULL, distance SMALLINT NOT NULL, hourly_rate NUMERIC(5, 2) NOT NULL, flat_rate NUMERIC(10, 2) NOT NULL, is_planning_person BOOLEAN NOT NULL, planning_hours SMALLINT NOT NULL, is_planning_material BOOLEAN NOT NULL, planning_cost_material NUMERIC(10, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_97E648F4E7121AF ON cantieri (provincia_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_97E648F5FE432E6 ON cantieri (regola_fatturazione_id)');
        $this->addSql('CREATE TABLE commenti_pubblici (id INT NOT NULL, cantieri_id INT NOT NULL, author VARCHAR(40) NOT NULL, email VARCHAR(100) NOT NULL, text_comment TEXT NOT NULL, photo_filename VARCHAR(255) DEFAULT NULL, state VARCHAR(30) DEFAULT \'submitted\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3FF734CC3A046B1C ON commenti_pubblici (cantieri_id)');
        $this->addSql('CREATE TABLE province (id INT NOT NULL, code VARCHAR(2) NOT NULL, name VARCHAR(40) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE regole_fatturazione (id INT NOT NULL, billing_cadence VARCHAR(20) NOT NULL, days_range SMALLINT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE cantieri ADD CONSTRAINT FK_97E648F4E7121AF FOREIGN KEY (provincia_id) REFERENCES province (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cantieri ADD CONSTRAINT FK_97E648F5FE432E6 FOREIGN KEY (regola_fatturazione_id) REFERENCES regole_fatturazione (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commenti_pubblici ADD CONSTRAINT FK_3FF734CC3A046B1C FOREIGN KEY (cantieri_id) REFERENCES cantieri (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commenti_pubblici DROP CONSTRAINT FK_3FF734CC3A046B1C');
        $this->addSql('ALTER TABLE cantieri DROP CONSTRAINT FK_97E648F4E7121AF');
        $this->addSql('ALTER TABLE cantieri DROP CONSTRAINT FK_97E648F5FE432E6');
        $this->addSql('DROP SEQUENCE cantieri_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE commenti_pubblici_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE province_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE regole_fatturazione_id_seq CASCADE');
        $this->addSql('DROP TABLE cantieri');
        $this->addSql('DROP TABLE commenti_pubblici');
        $this->addSql('DROP TABLE province');
        $this->addSql('DROP TABLE regole_fatturazione');
    }
}
