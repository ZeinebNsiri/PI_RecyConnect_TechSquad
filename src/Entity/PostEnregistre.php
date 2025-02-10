<?php

namespace App\Entity;

use App\Repository\PostEnregistreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostEnregistreRepository::class)]
class PostEnregistre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'postEnregistres_post')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post_enrg = null;

    #[ORM\ManyToOne(inversedBy: 'postEnregistres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user_enrg = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_enrg = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostEnrg(): ?Post
    {
        return $this->post_enrg;
    }

    public function setPostEnrg(?Post $post_enrg): static
    {
        $this->post_enrg = $post_enrg;

        return $this;
    }

    public function getUserEnrg(): ?Utilisateur
    {
        return $this->user_enrg;
    }

    public function setUserEnrg(?Utilisateur $user_enrg): static
    {
        $this->user_enrg = $user_enrg;

        return $this;
    }

    public function getDateEnrg(): ?\DateTimeInterface
    {
        return $this->date_enrg;
    }

    public function setDateEnrg(\DateTimeInterface $date_enrg): static
    {
        $this->date_enrg = $date_enrg;

        return $this;
    }
}
