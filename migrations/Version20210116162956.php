<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210116162956 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cantieri ALTER type_order_pa SET DEFAULT \'N\'');
        $this->addSql('ALTER TABLE personale ADD iban_conto VARCHAR(27) DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD intestatario_conto VARCHAR(60) DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ALTER fiscal_code SET DEFAULT \' \'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cantieri ALTER type_order_pa DROP DEFAULT');
        $this->addSql('ALTER TABLE personale DROP iban_conto');
        $this->addSql('ALTER TABLE personale DROP intestatario_conto');
        $this->addSql('ALTER TABLE personale ALTER fiscal_code SET DEFAULT \'CCCNNNAAMDDLZZZC\'');
    }
}
