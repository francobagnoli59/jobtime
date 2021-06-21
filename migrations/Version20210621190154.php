<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210621190154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moduli_raccolta_ore_cantieri ADD prev_id_planned SMALLINT DEFAULT 0');
        $this->addSql('ALTER TABLE moduli_raccolta_ore_cantieri DROP is_planned');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE moduli_raccolta_ore_cantieri ADD is_planned BOOLEAN DEFAULT \'true\'');
        $this->addSql('ALTER TABLE moduli_raccolta_ore_cantieri DROP prev_id_planned');
    }
}
