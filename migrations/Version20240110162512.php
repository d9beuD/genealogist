<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240110162512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_union_id INTEGER DEFAULT NULL, tree_id INTEGER NOT NULL, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(30) DEFAULT NULL, birth DATE DEFAULT NULL, death DATE DEFAULT NULL, birth_day_sure BOOLEAN NOT NULL, birth_month_sure BOOLEAN NOT NULL, birth_year_sure BOOLEAN NOT NULL, death_day_sure BOOLEAN NOT NULL, death_month_sure BOOLEAN NOT NULL, death_year_sure BOOLEAN NOT NULL, portrait VARCHAR(255) DEFAULT NULL, bio CLOB NOT NULL, CONSTRAINT FK_34DCD176B3B6F9E8 FOREIGN KEY (parent_union_id) REFERENCES "union" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_34DCD17678B64A2 FOREIGN KEY (tree_id) REFERENCES tree (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_34DCD176B3B6F9E8 ON person (parent_union_id)');
        $this->addSql('CREATE INDEX IDX_34DCD17678B64A2 ON person (tree_id)');
        $this->addSql('CREATE TABLE tree (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id INTEGER NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_B73E5EDC7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B73E5EDC7E3C61F9 ON tree (owner_id)');
        $this->addSql('CREATE TABLE "union" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, maried BOOLEAN NOT NULL, date DATE DEFAULT NULL)');
        $this->addSql('CREATE TABLE union_person (union_id INTEGER NOT NULL, person_id INTEGER NOT NULL, PRIMARY KEY(union_id, person_id), CONSTRAINT FK_FE54B7782C7B5539 FOREIGN KEY (union_id) REFERENCES "union" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FE54B778217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FE54B7782C7B5539 ON union_person (union_id)');
        $this->addSql('CREATE INDEX IDX_FE54B778217BBB47 ON union_person (person_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE tree');
        $this->addSql('DROP TABLE "union"');
        $this->addSql('DROP TABLE union_person');
    }
}
