<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210604142635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documenti_cantieri ADD tipologia VARCHAR(3) DEFAULT \'NUL\'');
        $this->addSql('ALTER TABLE documenti_personale ADD tipologia VARCHAR(3) DEFAULT \'NUL\'');
        $this->addSql('ALTER TABLE documenti_personale ADD scadenza DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD num_trasferte_italia SMALLINT DEFAULT 0');
        $this->addSql('ALTER TABLE personale ADD is_validated BOOLEAN DEFAULT \'false\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE personale DROP num_trasferte_italia');
        $this->addSql('ALTER TABLE personale DROP is_validated');
        $this->addSql('ALTER TABLE documenti_personale DROP tipologia');
        $this->addSql('ALTER TABLE documenti_personale DROP scadenza');
        $this->addSql('ALTER TABLE documenti_cantieri DROP tipologia');
    }
}
