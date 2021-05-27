<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210526133359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE attrezzature_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE movimenti_attrezzature_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE attrezzature (id INT NOT NULL, movimenti_attrezzature_id INT DEFAULT NULL, name VARCHAR(60) NOT NULL, funzione TEXT DEFAULT NULL, photo_attrezzo VARCHAR(255) DEFAULT NULL, is_out_of_order BOOLEAN DEFAULT \'false\' NOT NULL, data_acquisto DATE DEFAULT NULL, riferimenti_acquisto VARCHAR(255) DEFAULT NULL, riferimento_cespite VARCHAR(20) DEFAULT NULL, scadenza_manutenzione DATE DEFAULT NULL, costo NUMERIC(9, 2) DEFAULT \'0\', created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EF604BFB5E237E06 ON attrezzature (name)');
        $this->addSql('CREATE INDEX IDX_EF604BFB5C58573A ON attrezzature (movimenti_attrezzature_id)');
        $this->addSql('CREATE TABLE movimenti_attrezzature (id INT NOT NULL, data_movimento DATE NOT NULL, note TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE attrezzature ADD CONSTRAINT FK_EF604BFB5C58573A FOREIGN KEY (movimenti_attrezzature_id) REFERENCES movimenti_attrezzature (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cantieri ADD movimenti_attrezzature_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cantieri ADD CONSTRAINT FK_97E648F5C58573A FOREIGN KEY (movimenti_attrezzature_id) REFERENCES movimenti_attrezzature (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_97E648F5C58573A ON cantieri (movimenti_attrezzature_id)');
        $this->addSql('ALTER TABLE personale ADD movimenti_attrezzature_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD CONSTRAINT FK_5FEB70E5C58573A FOREIGN KEY (movimenti_attrezzature_id) REFERENCES movimenti_attrezzature (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5FEB70E5C58573A ON personale (movimenti_attrezzature_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE attrezzature DROP CONSTRAINT FK_EF604BFB5C58573A');
        $this->addSql('ALTER TABLE cantieri DROP CONSTRAINT FK_97E648F5C58573A');
        $this->addSql('ALTER TABLE personale DROP CONSTRAINT FK_5FEB70E5C58573A');
        $this->addSql('DROP SEQUENCE attrezzature_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE movimenti_attrezzature_id_seq CASCADE');
        $this->addSql('DROP TABLE attrezzature');
        $this->addSql('DROP TABLE movimenti_attrezzature');
        $this->addSql('DROP INDEX IDX_5FEB70E5C58573A');
        $this->addSql('ALTER TABLE personale DROP movimenti_attrezzature_id');
        $this->addSql('DROP INDEX IDX_97E648F5C58573A');
        $this->addSql('ALTER TABLE cantieri DROP movimenti_attrezzature_id');
    }
}
