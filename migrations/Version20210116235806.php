<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210116235806 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personale ADD azienda_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ALTER provincia_id SET NOT NULL');
        $this->addSql('ALTER TABLE personale ADD CONSTRAINT FK_5FEB70EEADEA542 FOREIGN KEY (azienda_id) REFERENCES aziende (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FEB70EEADEA542 ON personale (azienda_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE personale DROP CONSTRAINT FK_5FEB70EEADEA542');
        $this->addSql('DROP INDEX UNIQ_5FEB70EEADEA542');
        $this->addSql('ALTER TABLE personale DROP azienda_id');
        $this->addSql('ALTER TABLE personale ALTER provincia_id DROP NOT NULL');
    }
}
