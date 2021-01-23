<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210114224826 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cantieri ADD type_order_pa VARCHAR(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE cantieri ADD num_documento VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE cantieri ADD date_documento DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE cantieri ADD codice_cig VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE cantieri ADD codice_cup VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE cantieri ALTER maps_google TYPE VARCHAR(512)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cantieri DROP type_order_pa');
        $this->addSql('ALTER TABLE cantieri DROP num_documento');
        $this->addSql('ALTER TABLE cantieri DROP date_documento');
        $this->addSql('ALTER TABLE cantieri DROP codice_cig');
        $this->addSql('ALTER TABLE cantieri DROP codice_cup');
        $this->addSql('ALTER TABLE cantieri ALTER maps_google TYPE VARCHAR(1024)');
    }
}
