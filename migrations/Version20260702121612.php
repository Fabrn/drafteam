<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260702121612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE champion ADD image_square_path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE champion ADD image_splash_path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE champion DROP image_full');
        $this->addSql('ALTER TABLE champion DROP image_sprite');
        $this->addSql('ALTER TABLE champion DROP image_x');
        $this->addSql('ALTER TABLE champion DROP image_y');
        $this->addSql('ALTER TABLE champion DROP image_width');
        $this->addSql('ALTER TABLE champion DROP image_height');
        $this->addSql('ALTER TABLE draft ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE draft ALTER banned_lol_ids SET NOT NULL');
        $this->addSql('ALTER TABLE draft_ban ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE draft_pick ALTER created_at SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE champion ADD image_full VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE champion ADD image_sprite VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE champion ADD image_x INT NOT NULL');
        $this->addSql('ALTER TABLE champion ADD image_y INT NOT NULL');
        $this->addSql('ALTER TABLE champion ADD image_width INT NOT NULL');
        $this->addSql('ALTER TABLE champion ADD image_height INT NOT NULL');
        $this->addSql('ALTER TABLE champion DROP image_square_path');
        $this->addSql('ALTER TABLE champion DROP image_splash_path');
        $this->addSql('ALTER TABLE draft ALTER banned_lol_ids DROP NOT NULL');
        $this->addSql('ALTER TABLE draft ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE draft_ban ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE draft_pick ALTER created_at DROP NOT NULL');
    }
}
