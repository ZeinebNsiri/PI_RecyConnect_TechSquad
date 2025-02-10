<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titreCours = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descriptionCours = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $video = null;

    #[ORM\Column(length: 255)]
    private ?string $imageCours = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategorieCours $categorieC = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreCours(): ?string
    {
        return $this->titreCours;
    }

    public function setTitreCours(string $titreCours): static
    {
        $this->titreCours = $titreCours;

        return $this;
    }

    public function getDescriptionCours(): ?string
    {
        return $this->descriptionCours;
    }

    public function setDescriptionCours(string $descriptionCours): static
    {
        $this->descriptionCours = $descriptionCours;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): static
    {
        $this->video = $video;

        return $this;
    }

    public function getImageCours(): ?string
    {
        return $this->imageCours;
    }

    public function setImageCours(string $imageCours): static
    {
        $this->imageCours = $imageCours;

        return $this;
    }

    public function getCategorieC(): ?CategorieCours
    {
        return $this->categorieC;
    }

    public function setCategorieC(?CategorieCours $categorieC): static
    {
        $this->categorieC = $categorieC;

        return $this;
    }
}
