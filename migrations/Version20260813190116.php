<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260813190116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE champion (id INT AUTO_INCREMENT NOT NULL, lol_id VARCHAR(16) NOT NULL, lol_key VARCHAR(3) NOT NULL, image_square_path VARCHAR(255) NOT NULL, image_splash_path VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE champion_data (language VARCHAR(5) NOT NULL, name VARCHAR(16) NOT NULL, title VARCHAR(255) NOT NULL, champion_id INT NOT NULL, INDEX IDX_61A372DAFA7FD7EB (champion_id), PRIMARY KEY (champion_id, language)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE draft (id INT AUTO_INCREMENT NOT NULL, identifier BINARY(16) NOT NULL, blue_team_uuid BINARY(16) NOT NULL, red_team_uuid BINARY(16) NOT NULL, spectator_uuid BINARY(16) NOT NULL, status VARCHAR(255) DEFAULT \'creating\' NOT NULL, max_timer INT DEFAULT 60 NOT NULL, current_timer INT DEFAULT NULL, blue_team_ready_checked TINYINT DEFAULT 0 NOT NULL, red_team_ready_checked TINYINT DEFAULT 0 NOT NULL, is_sandbox TINYINT DEFAULT 0 NOT NULL, phase VARCHAR(255) DEFAULT \'blue_ban_1\' NOT NULL, banned_lol_ids JSON NOT NULL, name VARCHAR(32) NOT NULL, blue_team_name VARCHAR(32) NOT NULL, red_team_name VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_467C9694772E836A (identifier), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE draft_ban (side VARCHAR(255) NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, draft_id INT NOT NULL, champion_id INT NOT NULL, INDEX IDX_F81CA9F1E2F3C5D1 (draft_id), INDEX IDX_F81CA9F1FA7FD7EB (champion_id), PRIMARY KEY (draft_id, champion_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE draft_pick (side VARCHAR(255) NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, is_temporary TINYINT DEFAULT 0 NOT NULL, draft_id INT NOT NULL, champion_id INT NOT NULL, INDEX IDX_838D399FE2F3C5D1 (draft_id), INDEX IDX_838D399FFA7FD7EB (champion_id), PRIMARY KEY (draft_id, champion_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, identifier BINARY(16) NOT NULL, created_at DATETIME NOT NULL, discord_id VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649772E836A (identifier), UNIQUE INDEX UNIQ_8D93D64943349DE (discord_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE champion_data ADD CONSTRAINT FK_61A372DAFA7FD7EB FOREIGN KEY (champion_id) REFERENCES champion (id)');
        $this->addSql('ALTER TABLE draft_ban ADD CONSTRAINT FK_F81CA9F1E2F3C5D1 FOREIGN KEY (draft_id) REFERENCES draft (id)');
        $this->addSql('ALTER TABLE draft_ban ADD CONSTRAINT FK_F81CA9F1FA7FD7EB FOREIGN KEY (champion_id) REFERENCES champion (id)');
        $this->addSql('ALTER TABLE draft_pick ADD CONSTRAINT FK_838D399FE2F3C5D1 FOREIGN KEY (draft_id) REFERENCES draft (id)');
        $this->addSql('ALTER TABLE draft_pick ADD CONSTRAINT FK_838D399FFA7FD7EB FOREIGN KEY (champion_id) REFERENCES champion (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE champion_data DROP FOREIGN KEY FK_61A372DAFA7FD7EB');
        $this->addSql('ALTER TABLE draft_ban DROP FOREIGN KEY FK_F81CA9F1E2F3C5D1');
        $this->addSql('ALTER TABLE draft_ban DROP FOREIGN KEY FK_F81CA9F1FA7FD7EB');
        $this->addSql('ALTER TABLE draft_pick DROP FOREIGN KEY FK_838D399FE2F3C5D1');
        $this->addSql('ALTER TABLE draft_pick DROP FOREIGN KEY FK_838D399FFA7FD7EB');
        $this->addSql('DROP TABLE champion');
        $this->addSql('DROP TABLE champion_data');
        $this->addSql('DROP TABLE draft');
        $this->addSql('DROP TABLE draft_ban');
        $this->addSql('DROP TABLE draft_pick');
        $this->addSql('DROP TABLE user');
    }
}
