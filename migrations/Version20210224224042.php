<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210224224042 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE categorie_servizi_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE documenti_personale_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE categorie_servizi (id INT NOT NULL, categoria VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE documenti_personale (id INT NOT NULL, persona_id INT DEFAULT NULL, titolo VARCHAR(80) NOT NULL, documento_path VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_297E3C1AF5F88DB9 ON documenti_personale (persona_id)');
        $this->addSql('ALTER TABLE documenti_personale ADD CONSTRAINT FK_297E3C1AF5F88DB9 FOREIGN KEY (persona_id) REFERENCES personale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cantieri ADD categoria_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cantieri ADD codice_ipa VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE cantieri ALTER distance DROP NOT NULL');
        $this->addSql('ALTER TABLE cantieri ADD CONSTRAINT FK_97E648F3397707A FOREIGN KEY (categoria_id) REFERENCES categorie_servizi (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_97E648F3397707A ON cantieri (categoria_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cantieri DROP CONSTRAINT FK_97E648F3397707A');
        $this->addSql('DROP SEQUENCE categorie_servizi_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE documenti_personale_id_seq CASCADE');
        $this->addSql('DROP TABLE categorie_servizi');
        $this->addSql('DROP TABLE documenti_personale');
        $this->addSql('DROP INDEX IDX_97E648F3397707A');
        $this->addSql('ALTER TABLE cantieri DROP categoria_id');
        $this->addSql('ALTER TABLE cantieri DROP codice_ipa');
        $this->addSql('ALTER TABLE cantieri ALTER distance SET NOT NULL');
    }
}
