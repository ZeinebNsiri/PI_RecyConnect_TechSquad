<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\CategorieArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CategorieArticleRepository::class)]
#[UniqueEntity(fields: ['nom_categorie'], message: 'Ce nom de la catégorie existe déjà!')]
class CategorieArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $nom_categorie = null;

    #[ORM\Column(length: 255)]
    private ?string $description_categorie = null;

    #[ORM\Column(length: 255)]
    private ?string $image_categorie = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'categorie', orphanRemoval: true)]
    private Collection $list_articles;

   

    public function __construct()
    {
        $this->list_articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCategorie(): ?string
    {
        return $this->nom_categorie;
    }

    public function setNomCategorie(string $nom_categorie): static
    {
        $this->nom_categorie = $nom_categorie;

        return $this;
    }

    public function getDescriptionCategorie(): ?string
    {
        return $this->description_categorie;
    }

    public function setDescriptionCategorie(string $description_categorie): static
    {
        $this->description_categorie = $description_categorie;

        return $this;
    }

    public function getImageCategorie(): ?string
    {
        return $this->image_categorie;
    }

    public function setImageCategorie(string $image_categorie): static
    {
        $this->image_categorie = $image_categorie;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getListArticles(): Collection
    {
        return $this->list_articles;
    }

    public function addListArticle(Article $listArticle): static
    {
        if (!$this->list_articles->contains($listArticle)) {
            $this->list_articles->add($listArticle);
            $listArticle->setCategorie($this);
        }

        return $this;
    }

    public function removeListArticle(Article $listArticle): static
    {
        if ($this->list_articles->removeElement($listArticle)) {
            // set the owning side to null (unless already changed)
            if ($listArticle->getCategorie() === $this) {
                $listArticle->setCategorie(null);
            }
        }

        return $this;
    }

    public function __toString()
{
    return $this->getNomCategorie();
}


}
