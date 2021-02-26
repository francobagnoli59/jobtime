<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210225183352 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE documenti_cantieri_id_seq CASCADE');
        $this->addSql('DROP TABLE documenti_cantieri');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE documenti_cantieri_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE documenti_cantieri (id INT NOT NULL, cantiere_id INT DEFAULT NULL, titolo VARCHAR(80) NOT NULL, documento_path VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_506bfb3170d2d4a4 ON documenti_cantieri (cantiere_id)');
        $this->addSql('ALTER TABLE documenti_cantieri ADD CONSTRAINT fk_506bfb3170d2d4a4 FOREIGN KEY (cantiere_id) REFERENCES cantieri (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
