<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210225160246 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documenti_cantieri DROP CONSTRAINT FK_506BFB3170D2D4A4');
        $this->addSql('ALTER TABLE documenti_cantieri ADD CONSTRAINT FK_506BFB3170D2D4A4 FOREIGN KEY (cantiere_id) REFERENCES cantieri (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE documenti_cantieri DROP CONSTRAINT fk_506bfb3170d2d4a4');
        $this->addSql('ALTER TABLE documenti_cantieri ADD CONSTRAINT fk_506bfb3170d2d4a4 FOREIGN KEY (cantiere_id) REFERENCES cantieri (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
