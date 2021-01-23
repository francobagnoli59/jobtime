<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210116184626 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE aziende_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE aziende (id INT NOT NULL, provincia_id INT NOT NULL, company_name VARCHAR(80) NOT NULL, nick_name VARCHAR(30) NOT NULL, address VARCHAR(60) NOT NULL, zip_code VARCHAR(10) NOT NULL, city VARCHAR(60) NOT NULL, partita_iva VARCHAR(11) NOT NULL, fiscal_code VARCHAR(16) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FCCE79DB4E7121AF ON aziende (provincia_id)');
        $this->addSql('ALTER TABLE aziende ADD CONSTRAINT FK_FCCE79DB4E7121AF FOREIGN KEY (provincia_id) REFERENCES province (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personale ADD provincia_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD cantiere_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ALTER zip_code SET DEFAULT \'00000\'');
        $this->addSql('ALTER TABLE personale ADD CONSTRAINT FK_5FEB70E4E7121AF FOREIGN KEY (provincia_id) REFERENCES province (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personale ADD CONSTRAINT FK_5FEB70E70D2D4A4 FOREIGN KEY (cantiere_id) REFERENCES cantieri (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FEB70E4E7121AF ON personale (provincia_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FEB70E70D2D4A4 ON personale (cantiere_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE aziende_id_seq CASCADE');
        $this->addSql('DROP TABLE aziende');
        $this->addSql('ALTER TABLE personale DROP CONSTRAINT FK_5FEB70E4E7121AF');
        $this->addSql('ALTER TABLE personale DROP CONSTRAINT FK_5FEB70E70D2D4A4');
        $this->addSql('DROP INDEX UNIQ_5FEB70E4E7121AF');
        $this->addSql('DROP INDEX UNIQ_5FEB70E70D2D4A4');
        $this->addSql('ALTER TABLE personale DROP provincia_id');
        $this->addSql('ALTER TABLE personale DROP cantiere_id');
        $this->addSql('ALTER TABLE personale ALTER zip_code SET DEFAULT \' \'');
    }
}
