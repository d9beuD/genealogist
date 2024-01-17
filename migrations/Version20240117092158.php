<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240117092158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tree ADD COLUMN name VARCHAR(30) DEFAULT \'My family tree\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__tree AS SELECT id, owner_id, created_at FROM tree');
        $this->addSql('DROP TABLE tree');
        $this->addSql('CREATE TABLE tree (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id INTEGER NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_B73E5EDC7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO tree (id, owner_id, created_at) SELECT id, owner_id, created_at FROM __temp__tree');
        $this->addSql('DROP TABLE __temp__tree');
        $this->addSql('CREATE INDEX IDX_B73E5EDC7E3C61F9 ON tree (owner_id)');
    }
}
