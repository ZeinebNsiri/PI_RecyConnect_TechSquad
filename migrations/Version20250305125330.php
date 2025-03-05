<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250305125330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement ADD end_time TIME NOT NULL, ADD google_meet_link VARCHAR(255) DEFAULT NULL, ADD map_coordinates VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD tags JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE reservation ADD meeting_link VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement DROP end_time, DROP google_meet_link, DROP map_coordinates');
        $this->addSql('ALTER TABLE post DROP tags');
        $this->addSql('ALTER TABLE reservation DROP meeting_link');
    }
}
