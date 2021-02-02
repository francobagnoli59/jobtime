<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210128152652 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE causali_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE clienti_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE causali (id INT NOT NULL, code VARCHAR(5) NOT NULL, description VARCHAR(40) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE clienti (id INT NOT NULL, provincia_id INT NOT NULL, name VARCHAR(80) NOT NULL, nick_name VARCHAR(30) DEFAULT NULL, address VARCHAR(60) DEFAULT \' \' NOT NULL, zip_code VARCHAR(10) DEFAULT \'00000\' NOT NULL, city VARCHAR(60) DEFAULT \' \' NOT NULL, country VARCHAR(2) NOT NULL, partita_iva VARCHAR(11) DEFAULT NULL, fiscal_code VARCHAR(16) NOT NULL, type_cliente VARCHAR(2) NOT NULL, code_sdi VARCHAR(7) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FDAAD70E4E7121AF ON clienti (provincia_id)');
        $this->addSql('ALTER TABLE clienti ADD CONSTRAINT FK_FDAAD70E4E7121AF FOREIGN KEY (provincia_id) REFERENCES province (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cantieri ALTER azienda_id DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE causali_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE clienti_id_seq CASCADE');
        $this->addSql('DROP TABLE causali');
        $this->addSql('DROP TABLE clienti');
        $this->addSql('ALTER TABLE cantieri ALTER azienda_id SET DEFAULT 1');
    }
}
