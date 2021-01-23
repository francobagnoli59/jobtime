<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210107202750 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_97E648FFFCDE816 ON cantieri (name_job)');
        $this->addSql('ALTER TABLE province ALTER created_at DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4ADAD40B77153098 ON province (code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_388643C6DEBF430B ON regole_fatturazione (billing_cadence)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_4ADAD40B77153098');
        $this->addSql('ALTER TABLE province ALTER created_at SET NOT NULL');
        $this->addSql('DROP INDEX UNIQ_388643C6DEBF430B');
        $this->addSql('DROP INDEX UNIQ_97E648FFFCDE816');
    }
}
