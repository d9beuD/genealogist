<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240127103022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "union" ADD COLUMN day_unsure BOOLEAN DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE "union" ADD COLUMN month_unsure BOOLEAN DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE "union" ADD COLUMN year_unsure BOOLEAN DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__union AS SELECT id, married, wedding_date, wedding_place FROM "union"');
        $this->addSql('DROP TABLE "union"');
        $this->addSql('CREATE TABLE "union" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, married BOOLEAN NOT NULL, wedding_date DATE DEFAULT NULL, wedding_place VARCHAR(100) DEFAULT NULL)');
        $this->addSql('INSERT INTO "union" (id, married, wedding_date, wedding_place) SELECT id, married, wedding_date, wedding_place FROM __temp__union');
        $this->addSql('DROP TABLE __temp__union');
    }
}
