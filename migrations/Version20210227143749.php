<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210227143749 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE import_cantieri_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE import_cantieri (id INT NOT NULL, azienda_id INT NOT NULL, nota VARCHAR(255) DEFAULT NULL, path_import VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A0D1DA75EADEA542 ON import_cantieri (azienda_id)');
        $this->addSql('ALTER TABLE import_cantieri ADD CONSTRAINT FK_A0D1DA75EADEA542 FOREIGN KEY (azienda_id) REFERENCES aziende (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE import_cantieri_id_seq CASCADE');
        $this->addSql('DROP TABLE import_cantieri');
    }
}
