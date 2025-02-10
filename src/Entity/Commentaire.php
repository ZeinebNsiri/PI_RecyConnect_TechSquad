<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu_com = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user_com = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_com = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires_post')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post_com = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenuCom(): ?string
    {
        return $this->contenu_com;
    }

    public function setContenuCom(string $contenu_com): static
    {
        $this->contenu_com = $contenu_com;

        return $this;
    }

    public function getUserCom(): ?Utilisateur
    {
        return $this->user_com;
    }

    public function setUserCom(?Utilisateur $user_com): static
    {
        $this->user_com = $user_com;

        return $this;
    }

    public function getDateCom(): ?\DateTimeInterface
    {
        return $this->date_com;
    }

    public function setDateCom(\DateTimeInterface $date_com): static
    {
        $this->date_com = $date_com;

        return $this;
    }

    public function getPostCom(): ?Post
    {
        return $this->post_com;
    }

    public function setPostCom(?Post $post_com): static
    {
        $this->post_com = $post_com;

        return $this;
    }
}
