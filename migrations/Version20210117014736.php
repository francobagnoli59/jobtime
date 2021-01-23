<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210117014736 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_97e648f5fe432e6');
        $this->addSql('DROP INDEX uniq_97e648f4e7121af');
        $this->addSql('CREATE INDEX IDX_97E648F4E7121AF ON cantieri (provincia_id)');
        $this->addSql('CREATE INDEX IDX_97E648F5FE432E6 ON cantieri (regola_fatturazione_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX IDX_97E648F4E7121AF');
        $this->addSql('DROP INDEX IDX_97E648F5FE432E6');
        $this->addSql('CREATE UNIQUE INDEX uniq_97e648f5fe432e6 ON cantieri (regola_fatturazione_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_97e648f4e7121af ON cantieri (provincia_id)');
    }
}
