<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210117012901 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_fcce79db4e7121af');
        $this->addSql('CREATE INDEX IDX_FCCE79DB4E7121AF ON aziende (provincia_id)');
        $this->addSql('DROP INDEX uniq_5feb70eeadea542');
        $this->addSql('DROP INDEX uniq_5feb70e70d2d4a4');
        $this->addSql('DROP INDEX uniq_5feb70e4e7121af');
        $this->addSql('CREATE INDEX IDX_5FEB70E4E7121AF ON personale (provincia_id)');
        $this->addSql('CREATE INDEX IDX_5FEB70E70D2D4A4 ON personale (cantiere_id)');
        $this->addSql('CREATE INDEX IDX_5FEB70EEADEA542 ON personale (azienda_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX IDX_FCCE79DB4E7121AF');
        $this->addSql('CREATE UNIQUE INDEX uniq_fcce79db4e7121af ON aziende (provincia_id)');
        $this->addSql('DROP INDEX IDX_5FEB70E4E7121AF');
        $this->addSql('DROP INDEX IDX_5FEB70E70D2D4A4');
        $this->addSql('DROP INDEX IDX_5FEB70EEADEA542');
        $this->addSql('CREATE UNIQUE INDEX uniq_5feb70eeadea542 ON personale (azienda_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_5feb70e70d2d4a4 ON personale (cantiere_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_5feb70e4e7121af ON personale (provincia_id)');
    }
}
