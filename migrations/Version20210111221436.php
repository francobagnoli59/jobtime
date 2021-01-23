<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210111221436 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cantieri ALTER hourly_rate TYPE NUMERIC(7, 2)');
        $this->addSql('ALTER TABLE personale ADD curriculum_vitae VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ALTER full_cost_hour TYPE NUMERIC(7, 2)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cantieri ALTER hourly_rate TYPE NUMERIC(5, 2)');
        $this->addSql('ALTER TABLE personale DROP curriculum_vitae');
        $this->addSql('ALTER TABLE personale ALTER full_cost_hour TYPE NUMERIC(5, 2)');
    }
}
