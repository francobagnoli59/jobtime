<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210216235241 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mesi_aziendali ADD ore_lavoro NUMERIC(7, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE mesi_aziendali ADD ore_straordinario NUMERIC(7, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE mesi_aziendali ADD ore_improduttive NUMERIC(7, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE mesi_aziendali ADD ore_ininfluenti NUMERIC(7, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE mesi_aziendali ADD ore_pianificate NUMERIC(7, 2) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE mesi_aziendali DROP ore_lavoro');
        $this->addSql('ALTER TABLE mesi_aziendali DROP ore_straordinario');
        $this->addSql('ALTER TABLE mesi_aziendali DROP ore_improduttive');
        $this->addSql('ALTER TABLE mesi_aziendali DROP ore_ininfluenti');
        $this->addSql('ALTER TABLE mesi_aziendali DROP ore_pianificate');
    }
}
