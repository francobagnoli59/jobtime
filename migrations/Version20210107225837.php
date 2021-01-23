<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210107225837 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cantieri ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE commenti_pubblici ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE personale ADD address VARCHAR(60) DEFAULT \' \' NOT NULL');
        $this->addSql('ALTER TABLE personale ADD zip_code VARCHAR(10) DEFAULT \' \' NOT NULL');
        $this->addSql('ALTER TABLE personale ADD city VARCHAR(60) DEFAULT \' \' NOT NULL');
        $this->addSql('ALTER TABLE personale ADD fiscal_code VARCHAR(16) DEFAULT \'CCCNNNAAMDDLZZZC\' NOT NULL');
        $this->addSql('ALTER TABLE personale ADD matricola VARCHAR(6) DEFAULT \'000000\' NOT NULL');
        $this->addSql('ALTER TABLE personale ADD email VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD phone VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD mobile VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD photo_avatar VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD is_enforce BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->addSql('ALTER TABLE personale ADD date_hiring DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD date_dismissal DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD full_cost_hour NUMERIC(5, 2) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE personale ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE regole_fatturazione ALTER created_at DROP NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE regole_fatturazione ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE commenti_pubblici ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE cantieri ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE personale DROP address');
        $this->addSql('ALTER TABLE personale DROP zip_code');
        $this->addSql('ALTER TABLE personale DROP city');
        $this->addSql('ALTER TABLE personale DROP fiscal_code');
        $this->addSql('ALTER TABLE personale DROP matricola');
        $this->addSql('ALTER TABLE personale DROP email');
        $this->addSql('ALTER TABLE personale DROP phone');
        $this->addSql('ALTER TABLE personale DROP mobile');
        $this->addSql('ALTER TABLE personale DROP photo_avatar');
        $this->addSql('ALTER TABLE personale DROP is_enforce');
        $this->addSql('ALTER TABLE personale DROP date_hiring');
        $this->addSql('ALTER TABLE personale DROP date_dismissal');
        $this->addSql('ALTER TABLE personale DROP full_cost_hour');
        $this->addSql('ALTER TABLE personale ALTER created_at SET NOT NULL');
    }
}
