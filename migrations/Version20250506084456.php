<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250506084456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `character` ADD building_id INT DEFAULT NULL, CHANGE updated_at updated_at DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `character` ADD CONSTRAINT FK_937AB0344D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_937AB0344D2A7E12 ON `character` (building_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `character` DROP FOREIGN KEY FK_937AB0344D2A7E12
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_937AB0344D2A7E12 ON `character`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `character` DROP building_id, CHANGE updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }
}
