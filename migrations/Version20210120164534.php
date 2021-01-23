<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210120164534 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personale ADD cantiere_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD CONSTRAINT FK_5FEB70E70D2D4A4 FOREIGN KEY (cantiere_id) REFERENCES cantieri (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5FEB70E70D2D4A4 ON personale (cantiere_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE personale DROP CONSTRAINT FK_5FEB70E70D2D4A4');
        $this->addSql('DROP INDEX IDX_5FEB70E70D2D4A4');
        $this->addSql('ALTER TABLE personale DROP cantiere_id');
    }
}
