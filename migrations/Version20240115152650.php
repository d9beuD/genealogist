<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240115152650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person ADD COLUMN birth_place VARCHAR(60) DEFAULT NULL');
        $this->addSql('ALTER TABLE person ADD COLUMN death_place VARCHAR(60) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__person AS SELECT id, parent_union_id, tree_id, firstname, lastname, birth, death, birth_day_sure, birth_month_sure, birth_year_sure, death_day_sure, death_month_sure, death_year_sure, portrait, bio, gender, dead, birth_name, other_names FROM person');
        $this->addSql('DROP TABLE person');
        $this->addSql('CREATE TABLE person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_union_id INTEGER DEFAULT NULL, tree_id INTEGER NOT NULL, firstname VARCHAR(30) DEFAULT \'\', lastname VARCHAR(30) DEFAULT \'\', birth DATE DEFAULT NULL, death DATE DEFAULT NULL, birth_day_sure BOOLEAN NOT NULL, birth_month_sure BOOLEAN NOT NULL, birth_year_sure BOOLEAN NOT NULL, death_day_sure BOOLEAN NOT NULL, death_month_sure BOOLEAN NOT NULL, death_year_sure BOOLEAN NOT NULL, portrait VARCHAR(255) DEFAULT NULL, bio CLOB DEFAULT NULL, gender SMALLINT DEFAULT NULL, dead BOOLEAN DEFAULT 0 NOT NULL, birth_name VARCHAR(30) DEFAULT NULL, other_names VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_34DCD176B3B6F9E8 FOREIGN KEY (parent_union_id) REFERENCES "union" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_34DCD17678B64A2 FOREIGN KEY (tree_id) REFERENCES tree (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO person (id, parent_union_id, tree_id, firstname, lastname, birth, death, birth_day_sure, birth_month_sure, birth_year_sure, death_day_sure, death_month_sure, death_year_sure, portrait, bio, gender, dead, birth_name, other_names) SELECT id, parent_union_id, tree_id, firstname, lastname, birth, death, birth_day_sure, birth_month_sure, birth_year_sure, death_day_sure, death_month_sure, death_year_sure, portrait, bio, gender, dead, birth_name, other_names FROM __temp__person');
        $this->addSql('DROP TABLE __temp__person');
        $this->addSql('CREATE INDEX IDX_34DCD176B3B6F9E8 ON person (parent_union_id)');
        $this->addSql('CREATE INDEX IDX_34DCD17678B64A2 ON person (tree_id)');
    }
}
