<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216145948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B32F446BE');
        $this->addSql('DROP INDEX IDX_AC6340B32F446BE ON `like`');
        $this->addSql('ALTER TABLE `like` CHANGE ueser_like_id user_like_id INT NOT NULL');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B3DD96E438 FOREIGN KEY (user_like_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_AC6340B3DD96E438 ON `like` (user_like_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B3DD96E438');
        $this->addSql('DROP INDEX IDX_AC6340B3DD96E438 ON `like`');
        $this->addSql('ALTER TABLE `like` CHANGE user_like_id ueser_like_id INT NOT NULL');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B32F446BE FOREIGN KEY (ueser_like_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_AC6340B32F446BE ON `like` (ueser_like_id)');
    }
}
