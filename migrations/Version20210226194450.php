<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210226194450 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cantieri ALTER city DROP NOT NULL');
        $this->addSql('ALTER TABLE personale ALTER note_visita TYPE TEXT');
        $this->addSql('ALTER TABLE personale ALTER note_visita DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cantieri ALTER city SET NOT NULL');
        $this->addSql('ALTER TABLE personale ALTER note_visita TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE personale ALTER note_visita DROP DEFAULT');
    }
}
