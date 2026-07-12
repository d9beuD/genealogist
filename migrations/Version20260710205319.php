<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260710205319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorite_member (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_4C519682A76ED395 (user_id), INDEX IDX_4C519682217BBB47 (person_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(30) DEFAULT \'\', lastname VARCHAR(30) DEFAULT \'\', birth DATE DEFAULT NULL, death DATE DEFAULT NULL, birth_day_unsure TINYINT DEFAULT 0 NOT NULL, birth_month_unsure TINYINT DEFAULT 0 NOT NULL, birth_year_unsure TINYINT DEFAULT 0 NOT NULL, death_day_unsure TINYINT DEFAULT 0 NOT NULL, death_month_unsure TINYINT DEFAULT 0 NOT NULL, death_year_unsure TINYINT DEFAULT 0 NOT NULL, portrait VARCHAR(255) DEFAULT NULL, bio LONGTEXT DEFAULT NULL, gender SMALLINT DEFAULT NULL, dead TINYINT DEFAULT 0 NOT NULL, birth_name VARCHAR(30) DEFAULT NULL, other_names VARCHAR(255) DEFAULT NULL, birth_place VARCHAR(60) DEFAULT NULL, death_place VARCHAR(60) DEFAULT NULL, parent_union_id INT DEFAULT NULL, tree_id INT NOT NULL, INDEX IDX_34DCD176B3B6F9E8 (parent_union_id), INDEX IDX_34DCD17678B64A2 (tree_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE source (id INT AUTO_INCREMENT NOT NULL, type SMALLINT NOT NULL, url VARCHAR(2048) NOT NULL, comment VARCHAR(255) DEFAULT NULL, direct_proof TINYINT DEFAULT 0 NOT NULL, person_id INT NOT NULL, INDEX IDX_5F8A7F73217BBB47 (person_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tree (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, name VARCHAR(30) DEFAULT \'My family tree\' NOT NULL, user_id INT NOT NULL, INDEX IDX_B73E5EDCA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `union` (id INT AUTO_INCREMENT NOT NULL, married TINYINT NOT NULL, starts_at DATE DEFAULT NULL, place VARCHAR(100) DEFAULT NULL, day_unsure TINYINT DEFAULT 0 NOT NULL, month_unsure TINYINT DEFAULT 0 NOT NULL, year_unsure TINYINT DEFAULT 0 NOT NULL, description LONGTEXT DEFAULT NULL, ends_at DATETIME DEFAULT NULL, end_day_unsure TINYINT DEFAULT 0 NOT NULL, end_month_unsure TINYINT DEFAULT 0 NOT NULL, end_year_unsure TINYINT DEFAULT 0 NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE union_person (union_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_FE54B7782C7B5539 (union_id), INDEX IDX_FE54B778217BBB47 (person_id), PRIMARY KEY (union_id, person_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(30) NOT NULL, is_verified TINYINT NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE favorite_member ADD CONSTRAINT FK_4C519682A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE favorite_member ADD CONSTRAINT FK_4C519682217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176B3B6F9E8 FOREIGN KEY (parent_union_id) REFERENCES `union` (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD17678B64A2 FOREIGN KEY (tree_id) REFERENCES tree (id)');
        $this->addSql('ALTER TABLE source ADD CONSTRAINT FK_5F8A7F73217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE tree ADD CONSTRAINT FK_B73E5EDCA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE union_person ADD CONSTRAINT FK_FE54B7782C7B5539 FOREIGN KEY (union_id) REFERENCES `union` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE union_person ADD CONSTRAINT FK_FE54B778217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorite_member DROP FOREIGN KEY FK_4C519682A76ED395');
        $this->addSql('ALTER TABLE favorite_member DROP FOREIGN KEY FK_4C519682217BBB47');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176B3B6F9E8');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD17678B64A2');
        $this->addSql('ALTER TABLE source DROP FOREIGN KEY FK_5F8A7F73217BBB47');
        $this->addSql('ALTER TABLE tree DROP FOREIGN KEY FK_B73E5EDCA76ED395');
        $this->addSql('ALTER TABLE union_person DROP FOREIGN KEY FK_FE54B7782C7B5539');
        $this->addSql('ALTER TABLE union_person DROP FOREIGN KEY FK_FE54B778217BBB47');
        $this->addSql('DROP TABLE favorite_member');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE source');
        $this->addSql('DROP TABLE tree');
        $this->addSql('DROP TABLE `union`');
        $this->addSql('DROP TABLE union_person');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
