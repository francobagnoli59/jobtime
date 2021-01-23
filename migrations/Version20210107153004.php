<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210107153004 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE personale_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE personale (id INT NOT NULL, name VARCHAR(40) NOT NULL, surname VARCHAR(40) NOT NULL, gender VARCHAR(1) NOT NULL, birthday DATE DEFAULT NULL, plan_hour_week TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN personale.plan_hour_week IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE cantieri ADD maps_google VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE cantieri DROP maps');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE personale_id_seq CASCADE');
        $this->addSql('DROP TABLE personale');
        $this->addSql('ALTER TABLE cantieri ADD maps VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE cantieri DROP maps_google');
    }
}
