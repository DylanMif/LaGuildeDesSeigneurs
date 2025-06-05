<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250605072721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE building (id INT AUTO_INCREMENT NOT NULL, gls_name VARCHAR(20) NOT NULL, gls_slug VARCHAR(20) NOT NULL, gls_caste VARCHAR(20) DEFAULT NULL, gls_strength SMALLINT DEFAULT NULL, gls_image VARCHAR(50) DEFAULT NULL, gls_rating SMALLINT DEFAULT NULL, gls_identifier VARCHAR(40) NOT NULL, gls_creation DATETIME DEFAULT NULL, gls_modification DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `character` (id INT AUTO_INCREMENT NOT NULL, building_id INT DEFAULT NULL, user_id INT DEFAULT NULL, gls_name VARCHAR(20) NOT NULL, gls_surname VARCHAR(50) NOT NULL, gls_caste VARCHAR(20) DEFAULT NULL, gls_knowledge VARCHAR(20) NOT NULL, gls_intelligence SMALLINT DEFAULT NULL, gls_strength SMALLINT DEFAULT NULL, gls_image VARCHAR(50) DEFAULT NULL, gls_slug VARCHAR(20) NOT NULL, gls_kind VARCHAR(20) NOT NULL, gls_creation DATETIME NOT NULL, identifier VARCHAR(40) NOT NULL, gls_modification DATETIME NOT NULL, INDEX IDX_937AB0344D2A7E12 (building_id), INDEX IDX_937AB034A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, creation DATETIME NOT NULL, modification DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `character` ADD CONSTRAINT FK_937AB0344D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `character` ADD CONSTRAINT FK_937AB034A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `character` DROP FOREIGN KEY FK_937AB0344D2A7E12
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `character` DROP FOREIGN KEY FK_937AB034A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE building
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `character`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
    }
}
