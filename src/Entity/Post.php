<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user_p = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contenuMultimedia = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datePublication = null;

    #[ORM\Column]
    private ?int $nbrJaime = null;

    #[ORM\Column]
    private ?bool $status_post = null;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'post_com', orphanRemoval: true)]
    private Collection $commentaires_post;

    /**
     * @var Collection<int, PostEnregistre>
     */
    #[ORM\OneToMany(targetEntity: PostEnregistre::class, mappedBy: 'post_enrg', orphanRemoval: true)]
    private Collection $postEnregistres_post;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'post_like', orphanRemoval: true)]
    private Collection $likes_post;

    public function __construct()
    {
        $this->commentaires_post = new ArrayCollection();
        $this->postEnregistres_post = new ArrayCollection();
        $this->likes_post = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserP(): ?Utilisateur
    {
        return $this->user_p;
    }

    public function setUserP(?Utilisateur $user_p): static
    {
        $this->user_p = $user_p;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getContenuMultimedia(): ?string
    {
        return $this->contenuMultimedia;
    }

    public function setContenuMultimedia(?string $contenuMultimedia): static
    {
        $this->contenuMultimedia = $contenuMultimedia;

        return $this;
    }

    public function getDatePublication(): ?\DateTimeInterface
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTimeInterface $datePublication): static
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getNbrJaime(): ?int
    {
        return $this->nbrJaime;
    }

    public function setNbrJaime(int $nbrJaime): static
    {
        $this->nbrJaime = $nbrJaime;

        return $this;
    }

    public function isStatusPost(): ?bool
    {
        return $this->status_post;
    }

    public function setStatusPost(bool $status_post): static
    {
        $this->status_post = $status_post;

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentairesPost(): Collection
    {
        return $this->commentaires_post;
    }

    public function addCommentairesPost(Commentaire $commentairesPost): static
    {
        if (!$this->commentaires_post->contains($commentairesPost)) {
            $this->commentaires_post->add($commentairesPost);
            $commentairesPost->setPostCom($this);
        }

        return $this;
    }

    public function removeCommentairesPost(Commentaire $commentairesPost): static
    {
        if ($this->commentaires_post->removeElement($commentairesPost)) {
            // set the owning side to null (unless already changed)
            if ($commentairesPost->getPostCom() === $this) {
                $commentairesPost->setPostCom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostEnregistre>
     */
    public function getPostEnregistresPost(): Collection
    {
        return $this->postEnregistres_post;
    }

    public function addPostEnregistresPost(PostEnregistre $postEnregistresPost): static
    {
        if (!$this->postEnregistres_post->contains($postEnregistresPost)) {
            $this->postEnregistres_post->add($postEnregistresPost);
            $postEnregistresPost->setPostEnrg($this);
        }

        return $this;
    }

    public function removePostEnregistresPost(PostEnregistre $postEnregistresPost): static
    {
        if ($this->postEnregistres_post->removeElement($postEnregistresPost)) {
            // set the owning side to null (unless already changed)
            if ($postEnregistresPost->getPostEnrg() === $this) {
                $postEnregistresPost->setPostEnrg(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikesPost(): Collection
    {
        return $this->likes_post;
    }

    public function addLikesPost(Like $likesPost): static
    {
        if (!$this->likes_post->contains($likesPost)) {
            $this->likes_post->add($likesPost);
            $likesPost->setPostLike($this);
        }

        return $this;
    }

    public function removeLikesPost(Like $likesPost): static
    {
        if ($this->likes_post->removeElement($likesPost)) {
            // set the owning side to null (unless already changed)
            if ($likesPost->getPostLike() === $this) {
                $likesPost->setPostLike(null);
            }
        }

        return $this;
    }
}
