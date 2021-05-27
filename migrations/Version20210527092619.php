<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210527092619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attrezzature DROP CONSTRAINT fk_ef604bfb5c58573a');
        $this->addSql('DROP INDEX idx_ef604bfb5c58573a');
        $this->addSql('ALTER TABLE attrezzature DROP movimenti_attrezzature_id');
        $this->addSql('ALTER TABLE cantieri DROP CONSTRAINT fk_97e648f5c58573a');
        $this->addSql('DROP INDEX idx_97e648f5c58573a');
        $this->addSql('ALTER TABLE cantieri DROP movimenti_attrezzature_id');
        $this->addSql('ALTER TABLE movimenti_attrezzature ADD attrezzatura_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE movimenti_attrezzature ADD cantiere_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE movimenti_attrezzature ADD persona_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE movimenti_attrezzature ADD CONSTRAINT FK_572C904CE8D392F FOREIGN KEY (attrezzatura_id) REFERENCES attrezzature (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movimenti_attrezzature ADD CONSTRAINT FK_572C904C70D2D4A4 FOREIGN KEY (cantiere_id) REFERENCES cantieri (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movimenti_attrezzature ADD CONSTRAINT FK_572C904CF5F88DB9 FOREIGN KEY (persona_id) REFERENCES personale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_572C904CE8D392F ON movimenti_attrezzature (attrezzatura_id)');
        $this->addSql('CREATE INDEX IDX_572C904C70D2D4A4 ON movimenti_attrezzature (cantiere_id)');
        $this->addSql('CREATE INDEX IDX_572C904CF5F88DB9 ON movimenti_attrezzature (persona_id)');
        $this->addSql('ALTER TABLE personale DROP CONSTRAINT fk_5feb70e5c58573a');
        $this->addSql('DROP INDEX idx_5feb70e5c58573a');
        $this->addSql('ALTER TABLE personale DROP movimenti_attrezzature_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cantieri ADD movimenti_attrezzature_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cantieri ADD CONSTRAINT fk_97e648f5c58573a FOREIGN KEY (movimenti_attrezzature_id) REFERENCES movimenti_attrezzature (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_97e648f5c58573a ON cantieri (movimenti_attrezzature_id)');
        $this->addSql('ALTER TABLE personale ADD movimenti_attrezzature_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personale ADD CONSTRAINT fk_5feb70e5c58573a FOREIGN KEY (movimenti_attrezzature_id) REFERENCES movimenti_attrezzature (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5feb70e5c58573a ON personale (movimenti_attrezzature_id)');
        $this->addSql('ALTER TABLE movimenti_attrezzature DROP CONSTRAINT FK_572C904CE8D392F');
        $this->addSql('ALTER TABLE movimenti_attrezzature DROP CONSTRAINT FK_572C904C70D2D4A4');
        $this->addSql('ALTER TABLE movimenti_attrezzature DROP CONSTRAINT FK_572C904CF5F88DB9');
        $this->addSql('DROP INDEX IDX_572C904CE8D392F');
        $this->addSql('DROP INDEX IDX_572C904C70D2D4A4');
        $this->addSql('DROP INDEX IDX_572C904CF5F88DB9');
        $this->addSql('ALTER TABLE movimenti_attrezzature DROP attrezzatura_id');
        $this->addSql('ALTER TABLE movimenti_attrezzature DROP cantiere_id');
        $this->addSql('ALTER TABLE movimenti_attrezzature DROP persona_id');
        $this->addSql('ALTER TABLE attrezzature ADD movimenti_attrezzature_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE attrezzature ADD CONSTRAINT fk_ef604bfb5c58573a FOREIGN KEY (movimenti_attrezzature_id) REFERENCES movimenti_attrezzature (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_ef604bfb5c58573a ON attrezzature (movimenti_attrezzature_id)');
    }
}
