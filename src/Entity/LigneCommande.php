<?php

namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneCommandeRepository::class)]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user_c = null;

    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article_c = null;

    #[ORM\Column]
    private ?int $quantite_c = null;

    #[ORM\Column(nullable: true)]
    private ?float $prix_c = null;

    #[ORM\Column(length: 255)]
    private ?string $etat_c = null;

    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    private ?Commande $commande_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserC(): ?Utilisateur
    {
        return $this->user_c;
    }

    public function setUserC(?Utilisateur $user_c): static
    {
        $this->user_c = $user_c;

        return $this;
    }

    public function getArticleC(): ?Article
    {
        return $this->article_c;
    }

    public function setArticleC(?Article $article_c): static
    {
        $this->article_c = $article_c;

        return $this;
    }

    public function getQuantiteC(): ?int
    {
        return $this->quantite_c;
    }

    public function setQuantiteC(int $quantite_c): static
    {
        $this->quantite_c = $quantite_c;

        return $this;
    }

    public function getPrixC(): ?float
    {
        return $this->prix_c;
    }

    public function setPrixC(float $prix_c): static
    {
        $this->prix_c = $prix_c;

        return $this;
    }

    public function getEtatC(): ?string
    {
        return $this->etat_c;
    }

    public function setEtatC(string $etat_c): static
    {
        $this->etat_c = $etat_c;

        return $this;
    }

    public function getCommandeId(): ?Commande
    {
        return $this->commande_id;
    }

    public function setCommandeId(?Commande $commande_id): static
    {
        $this->commande_id = $commande_id;

        return $this;
    }
}
