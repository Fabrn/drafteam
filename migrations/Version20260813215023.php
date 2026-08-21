<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260813215023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du profil Discord sur les utilisateurs';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D64943349DE ON user');
        $this->addSql('ALTER TABLE user ADD discord_profile_id VARCHAR(255) DEFAULT NULL, ADD discord_profile_username VARCHAR(255) DEFAULT NULL, ADD discord_profile_global_username VARCHAR(255) DEFAULT NULL, ADD discord_profile_locale VARCHAR(2) DEFAULT NULL, ADD discord_profile_avatar_hash VARCHAR(255) DEFAULT NULL, DROP discord_id, DROP discord_username');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649D345DDF2 ON user (discord_profile_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D649D345DDF2 ON user');
        $this->addSql('ALTER TABLE user ADD discord_id VARCHAR(255) DEFAULT NULL, ADD discord_username VARCHAR(255) DEFAULT NULL, DROP discord_profile_id, DROP discord_profile_username, DROP discord_profile_global_username, DROP discord_profile_locale, DROP discord_profile_avatar_hash');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64943349DE ON user (discord_id)');
    }
}
