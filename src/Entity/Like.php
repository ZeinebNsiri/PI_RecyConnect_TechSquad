<?php

namespace App\Entity;

use App\Repository\LikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikeRepository::class)]
#[ORM\Table(name: '`like`')]
class Like
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'likes_post')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post_like = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user_like = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostLike(): ?Post
    {
        return $this->post_like;
    }

    public function setPostLike(?Post $post_like): static
    {
        $this->post_like = $post_like;

        return $this;
    }

    public function getUserLike(): ?Utilisateur
    {
        return $this->user_like;
    }

    public function setUserLike(?Utilisateur $ueser_like): static
    {
        $this->user_like = $ueser_like;

        return $this;
    }
}
