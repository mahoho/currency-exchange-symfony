<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241110190016 extends AbstractMigration {
    public function getDescription(): string {
        return '';
    }

    public function up(Schema $schema): void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exchange_rates (id INT AUTO_INCREMENT NOT NULL, source_id INT DEFAULT NULL, rate NUMERIC(30, 5) NOT NULL, currency_code VARCHAR(3) NOT NULL, base_currency_code VARCHAR(3) NOT NULL, date DATE NOT NULL, INDEX IDX_5AE3E774953C1C61 (source_id), INDEX idx_exchange_rates_date (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rates_sources (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(1000) NOT NULL, is_default TINYINT(1) NOT NULL, base_currency_code VARCHAR(3) NOT NULL, INDEX idx_rate_sources_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE exchange_rates ADD CONSTRAINT FK_5AE3E774953C1C61 FOREIGN KEY (source_id) REFERENCES rates_sources (id)');
    }

    public function down(Schema $schema): void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exchange_rates DROP FOREIGN KEY FK_5AE3E774953C1C61');
        $this->addSql('DROP TABLE exchange_rates');
        $this->addSql('DROP TABLE rates_sources');
    }
}
