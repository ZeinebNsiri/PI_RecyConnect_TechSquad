<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226124710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE media_post (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, chemin VARCHAR(255) NOT NULL, INDEX IDX_99CDB35E4B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE media_post ADD CONSTRAINT FK_99CDB35E4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE commande ADD statut VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE commentaire ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC727ACA70 FOREIGN KEY (parent_id) REFERENCES commentaire (id)');
        $this->addSql('CREATE INDEX IDX_67F068BC727ACA70 ON commentaire (parent_id)');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B32F446BE');
        $this->addSql('DROP INDEX IDX_AC6340B32F446BE ON `like`');
        $this->addSql('ALTER TABLE `like` CHANGE ueser_like_id user_like_id INT NOT NULL');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B3DD96E438 FOREIGN KEY (user_like_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_AC6340B3DD96E438 ON `like` (user_like_id)');
        $this->addSql('ALTER TABLE post DROP contenu_multimedia');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media_post DROP FOREIGN KEY FK_99CDB35E4B89032C');
        $this->addSql('DROP TABLE media_post');
        $this->addSql('ALTER TABLE commande DROP statut');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC727ACA70');
        $this->addSql('DROP INDEX IDX_67F068BC727ACA70 ON commentaire');
        $this->addSql('ALTER TABLE commentaire DROP parent_id');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B3DD96E438');
        $this->addSql('DROP INDEX IDX_AC6340B3DD96E438 ON `like`');
        $this->addSql('ALTER TABLE `like` CHANGE user_like_id ueser_like_id INT NOT NULL');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B32F446BE FOREIGN KEY (ueser_like_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_AC6340B32F446BE ON `like` (ueser_like_id)');
        $this->addSql('ALTER TABLE post ADD contenu_multimedia VARCHAR(255) DEFAULT NULL');
    }
}
