<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210206223816 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3A1F2C68C6E493B0 ON festivita_annuali (anno)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DFF8B3DCD365D469 ON mesi_aziendali (key_reference)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8F4C8377D365D469 ON ore_lavorate (key_reference)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_3A1F2C68C6E493B0');
        $this->addSql('DROP INDEX UNIQ_DFF8B3DCD365D469');
        $this->addSql('DROP INDEX UNIQ_8F4C8377D365D469');
    }
}
