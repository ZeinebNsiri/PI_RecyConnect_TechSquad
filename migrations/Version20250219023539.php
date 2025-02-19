<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219023539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, categorie_id INT NOT NULL, utilisateur_id INT NOT NULL, nom_article VARCHAR(255) NOT NULL, description_article LONGTEXT NOT NULL, quantite_article INT NOT NULL, prix DOUBLE PRECISION NOT NULL, image_article VARCHAR(255) NOT NULL, localisation_article VARCHAR(255) NOT NULL, INDEX IDX_23A0E66BCF5E72D (categorie_id), INDEX IDX_23A0E66FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie_article (id INT AUTO_INCREMENT NOT NULL, nom_categorie VARCHAR(255) NOT NULL, description_categorie VARCHAR(255) NOT NULL, image_categorie VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5DB9A0C4DD8CA775 (nom_categorie), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie_cours (id INT AUTO_INCREMENT NOT NULL, nom_categorie VARCHAR(255) NOT NULL, description_categorie LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_8B2614CDD8CA775 (nom_categorie), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, prix_total DOUBLE PRECISION DEFAULT NULL, date_commande DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, user_com_id INT NOT NULL, post_com_id INT NOT NULL, parent_id INT DEFAULT NULL, contenu_com LONGTEXT NOT NULL, date_com DATETIME NOT NULL, INDEX IDX_67F068BC11DE140A (user_com_id), INDEX IDX_67F068BC44AEEB52 (post_com_id), INDEX IDX_67F068BC727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, categorie_c_id INT NOT NULL, titre_cours VARCHAR(255) NOT NULL, description_cours LONGTEXT NOT NULL, video VARCHAR(255) DEFAULT NULL, image_cours VARCHAR(255) NOT NULL, INDEX IDX_FDCA8C9CE6FE1C84 (categorie_c_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, nom_event VARCHAR(255) NOT NULL, description_event LONGTEXT NOT NULL, lieu_event VARCHAR(255) NOT NULL, date_event DATE NOT NULL, heure_event TIME NOT NULL, image_event VARCHAR(255) NOT NULL, capacite INT NOT NULL, nb_restant INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne_commande (id INT AUTO_INCREMENT NOT NULL, user_c_id INT NOT NULL, article_c_id INT NOT NULL, commande_id_id INT DEFAULT NULL, quantite_c INT NOT NULL, prix_c DOUBLE PRECISION DEFAULT NULL, etat_c VARCHAR(255) NOT NULL, INDEX IDX_3170B74BEB56D71A (user_c_id), INDEX IDX_3170B74B21951CD2 (article_c_id), INDEX IDX_3170B74B462C4194 (commande_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `like` (id INT AUTO_INCREMENT NOT NULL, post_like_id INT NOT NULL, user_like_id INT NOT NULL, INDEX IDX_AC6340B3B8734D01 (post_like_id), INDEX IDX_AC6340B3DD96E438 (user_like_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_post (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, chemin VARCHAR(255) NOT NULL, INDEX IDX_99CDB35E4B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, user_p_id INT NOT NULL, contenu LONGTEXT NOT NULL, date_publication DATETIME NOT NULL, nbr_jaime INT NOT NULL, status_post TINYINT(1) NOT NULL, INDEX IDX_5A8A6C8DA9FA2F6B (user_p_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_enregistre (id INT AUTO_INCREMENT NOT NULL, post_enrg_id INT NOT NULL, user_enrg_id INT NOT NULL, date_enrg DATETIME NOT NULL, INDEX IDX_DF166A99F7170512 (post_enrg_id), INDEX IDX_DF166A9992F2AC2B (user_enrg_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, user_id_id INT DEFAULT NULL, nb_places INT NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, num_tel VARCHAR(20) NOT NULL, demandes_speciales LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, INDEX IDX_42C8495571F7E88B (event_id), INDEX IDX_42C849559D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', nom_user VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, num_tel VARCHAR(20) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, matricule_fiscale VARCHAR(255) DEFAULT NULL, status TINYINT(1) NOT NULL, photo_profil VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B35DB375C4 (matricule_fiscale), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_article (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC11DE140A FOREIGN KEY (user_com_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC44AEEB52 FOREIGN KEY (post_com_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC727ACA70 FOREIGN KEY (parent_id) REFERENCES commentaire (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CE6FE1C84 FOREIGN KEY (categorie_c_id) REFERENCES categorie_cours (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BEB56D71A FOREIGN KEY (user_c_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B21951CD2 FOREIGN KEY (article_c_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B462C4194 FOREIGN KEY (commande_id_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B3B8734D01 FOREIGN KEY (post_like_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B3DD96E438 FOREIGN KEY (user_like_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE media_post ADD CONSTRAINT FK_99CDB35E4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA9FA2F6B FOREIGN KEY (user_p_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE post_enregistre ADD CONSTRAINT FK_DF166A99F7170512 FOREIGN KEY (post_enrg_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post_enregistre ADD CONSTRAINT FK_DF166A9992F2AC2B FOREIGN KEY (user_enrg_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495571F7E88B FOREIGN KEY (event_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849559D86650F FOREIGN KEY (user_id_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66BCF5E72D');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66FB88E14F');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC11DE140A');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC44AEEB52');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC727ACA70');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CE6FE1C84');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BEB56D71A');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B21951CD2');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B462C4194');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B3B8734D01');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B3DD96E438');
        $this->addSql('ALTER TABLE media_post DROP FOREIGN KEY FK_99CDB35E4B89032C');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA9FA2F6B');
        $this->addSql('ALTER TABLE post_enregistre DROP FOREIGN KEY FK_DF166A99F7170512');
        $this->addSql('ALTER TABLE post_enregistre DROP FOREIGN KEY FK_DF166A9992F2AC2B');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495571F7E88B');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849559D86650F');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE categorie_article');
        $this->addSql('DROP TABLE categorie_cours');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('DROP TABLE `like`');
        $this->addSql('DROP TABLE media_post');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_enregistre');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
