<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210204191607 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE festivita_annuali_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mesi_aziendali_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE festivita_annuali (id INT NOT NULL, anno VARCHAR(4) NOT NULL, date_festivita TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN festivita_annuali.date_festivita IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE mesi_aziendali (id INT NOT NULL, azienda_id INT NOT NULL, festivita_annuale_id INT NOT NULL, mese VARCHAR(2) NOT NULL, cost_month_human NUMERIC(12, 2) DEFAULT NULL, cost_month_material NUMERIC(12, 2) DEFAULT NULL, income_month NUMERIC(12, 2) DEFAULT NULL, is_hours_completed BOOLEAN DEFAULT \'false\' NOT NULL, is_invoices_completed BOOLEAN DEFAULT \'false\' NOT NULL, key_reference VARCHAR(30) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DFF8B3DCEADEA542 ON mesi_aziendali (azienda_id)');
        $this->addSql('CREATE INDEX IDX_DFF8B3DCDE6A8671 ON mesi_aziendali (festivita_annuale_id)');
        $this->addSql('ALTER TABLE mesi_aziendali ADD CONSTRAINT FK_DFF8B3DCEADEA542 FOREIGN KEY (azienda_id) REFERENCES aziende (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mesi_aziendali ADD CONSTRAINT FK_DFF8B3DCDE6A8671 FOREIGN KEY (festivita_annuale_id) REFERENCES festivita_annuali (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE mesi_aziendali DROP CONSTRAINT FK_DFF8B3DCDE6A8671');
        $this->addSql('DROP SEQUENCE festivita_annuali_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mesi_aziendali_id_seq CASCADE');
        $this->addSql('DROP TABLE festivita_annuali');
        $this->addSql('DROP TABLE mesi_aziendali');
    }
}
