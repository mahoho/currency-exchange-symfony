<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241109144915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exchange_rate (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, source_id INTEGER DEFAULT NULL, rate NUMERIC(30, 5) NOT NULL, currency_code VARCHAR(3) NOT NULL, base_currency_code VARCHAR(3) NOT NULL, date DATE NOT NULL, CONSTRAINT FK_E9521FAB953C1C61 FOREIGN KEY (source_id) REFERENCES rate_source (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E9521FAB953C1C61 ON exchange_rate (source_id)');
        $this->addSql('CREATE INDEX idx_exchange_rates_date ON exchange_rate (date)');
        $this->addSql('CREATE TABLE rate_source (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(1000) NOT NULL, is_default BOOLEAN NOT NULL, base_currency VARCHAR(3) NOT NULL)');
        $this->addSql('CREATE INDEX idx_rate_sources_name ON rate_source (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE exchange_rate');
        $this->addSql('DROP TABLE rate_source');
    }
}
