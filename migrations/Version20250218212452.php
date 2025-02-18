<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218212452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5DB9A0C4DD8CA775 ON categorie_article (nom_categorie)');
        $this->addSql('ALTER TABLE utilisateur CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE matricule_fiscale matricule_fiscale VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B35DB375C4 ON utilisateur (matricule_fiscale)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_5DB9A0C4DD8CA775 ON categorie_article');
        $this->addSql('DROP INDEX UNIQ_1D1C63B35DB375C4 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur CHANGE adresse adresse VARCHAR(255) NOT NULL, CHANGE matricule_fiscale matricule_fiscale VARCHAR(255) NOT NULL');
    }
}
