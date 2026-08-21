<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260820171430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gedcom_identifier (id INT AUTO_INCREMENT NOT NULL, record_type VARCHAR(16) NOT NULL, external_id VARCHAR(255) NOT NULL, import_id INT NOT NULL, person_id INT DEFAULT NULL, union_id INT DEFAULT NULL, INDEX IDX_1E2A068B6A263D9 (import_id), INDEX IDX_1E2A068217BBB47 (person_id), INDEX IDX_1E2A0682C7B5539 (union_id), UNIQUE INDEX uniq_gedcom_identifier (import_id, record_type, external_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE gedcom_import (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, format VARCHAR(16) NOT NULL, imported_at DATETIME NOT NULL, tree_id INT NOT NULL, INDEX IDX_27FDD6AA78B64A2 (tree_id), UNIQUE INDEX uniq_gedcom_import_tree_filename (tree_id, filename), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE gedcom_metadata (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(16) NOT NULL, external_id VARCHAR(255) DEFAULT NULL, payload JSON NOT NULL, import_id INT NOT NULL, person_id INT DEFAULT NULL, union_id INT DEFAULT NULL, INDEX IDX_F7FC2E8EB6A263D9 (import_id), INDEX IDX_F7FC2E8E217BBB47 (person_id), INDEX IDX_F7FC2E8E2C7B5539 (union_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE gedcom_identifier ADD CONSTRAINT FK_1E2A068B6A263D9 FOREIGN KEY (import_id) REFERENCES gedcom_import (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gedcom_identifier ADD CONSTRAINT FK_1E2A068217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gedcom_identifier ADD CONSTRAINT FK_1E2A0682C7B5539 FOREIGN KEY (union_id) REFERENCES `union` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gedcom_import ADD CONSTRAINT FK_27FDD6AA78B64A2 FOREIGN KEY (tree_id) REFERENCES tree (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gedcom_metadata ADD CONSTRAINT FK_F7FC2E8EB6A263D9 FOREIGN KEY (import_id) REFERENCES gedcom_import (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gedcom_metadata ADD CONSTRAINT FK_F7FC2E8E217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gedcom_metadata ADD CONSTRAINT FK_F7FC2E8E2C7B5539 FOREIGN KEY (union_id) REFERENCES `union` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person ADD birth_gedcom_date VARCHAR(255) DEFAULT NULL, ADD death_gedcom_date VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `union` ADD starts_at_gedcom_date VARCHAR(255) DEFAULT NULL, ADD ends_at_gedcom_date VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gedcom_identifier DROP FOREIGN KEY FK_1E2A068B6A263D9');
        $this->addSql('ALTER TABLE gedcom_identifier DROP FOREIGN KEY FK_1E2A068217BBB47');
        $this->addSql('ALTER TABLE gedcom_identifier DROP FOREIGN KEY FK_1E2A0682C7B5539');
        $this->addSql('ALTER TABLE gedcom_import DROP FOREIGN KEY FK_27FDD6AA78B64A2');
        $this->addSql('ALTER TABLE gedcom_metadata DROP FOREIGN KEY FK_F7FC2E8EB6A263D9');
        $this->addSql('ALTER TABLE gedcom_metadata DROP FOREIGN KEY FK_F7FC2E8E217BBB47');
        $this->addSql('ALTER TABLE gedcom_metadata DROP FOREIGN KEY FK_F7FC2E8E2C7B5539');
        $this->addSql('DROP TABLE gedcom_identifier');
        $this->addSql('DROP TABLE gedcom_import');
        $this->addSql('DROP TABLE gedcom_metadata');
        $this->addSql('ALTER TABLE person DROP birth_gedcom_date, DROP death_gedcom_date');
        $this->addSql('ALTER TABLE `union` DROP starts_at_gedcom_date, DROP ends_at_gedcom_date');
    }
}
