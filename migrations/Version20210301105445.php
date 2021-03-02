<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210301105445 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mansioni DROP CONSTRAINT fk_453472267a9a1aee');
        $this->addSql('DROP INDEX idx_453472267a9a1aee');
        $this->addSql('ALTER TABLE mansioni DROP persone_id');
        $this->addSql('ALTER TABLE mansioni RENAME COLUMN mansione TO mansione_name');
        $this->addSql('ALTER TABLE personale ADD mansione_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD CONSTRAINT FK_5FEB70E2CEAAF5E FOREIGN KEY (mansione_id) REFERENCES mansioni (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5FEB70E2CEAAF5E ON personale (mansione_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE mansioni ADD persone_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mansioni RENAME COLUMN mansione_name TO mansione');
        $this->addSql('ALTER TABLE mansioni ADD CONSTRAINT fk_453472267a9a1aee FOREIGN KEY (persone_id) REFERENCES personale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_453472267a9a1aee ON mansioni (persone_id)');
        $this->addSql('ALTER TABLE personale DROP CONSTRAINT FK_5FEB70E2CEAAF5E');
        $this->addSql('DROP INDEX IDX_5FEB70E2CEAAF5E');
        $this->addSql('ALTER TABLE personale DROP mansione_id');
    }
}
