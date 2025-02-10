<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_event = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description_event = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu_event = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_event = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heure_event = null;

    #[ORM\Column(length: 255)]
    private ?string $image_event = null;

    #[ORM\Column]
    private ?int $capacite = null;

    #[ORM\Column]
    private ?int $nb_restant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_AT = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated_AT = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'event_id', orphanRemoval: true)]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEvent(): ?string
    {
        return $this->nom_event;
    }

    public function setNomEvent(string $nom_event): static
    {
        $this->nom_event = $nom_event;

        return $this;
    }

    public function getDescriptionEvent(): ?string
    {
        return $this->description_event;
    }

    public function setDescriptionEvent(string $description_event): static
    {
        $this->description_event = $description_event;

        return $this;
    }

    public function getLieuEvent(): ?string
    {
        return $this->lieu_event;
    }

    public function setLieuEvent(string $lieu_event): static
    {
        $this->lieu_event = $lieu_event;

        return $this;
    }

    public function getDateEvent(): ?\DateTimeInterface
    {
        return $this->date_event;
    }

    public function setDateEvent(\DateTimeInterface $date_event): static
    {
        $this->date_event = $date_event;

        return $this;
    }

    public function getHeureEvent(): ?\DateTimeInterface
    {
        return $this->heure_event;
    }

    public function setHeureEvent(\DateTimeInterface $heure_event): static
    {
        $this->heure_event = $heure_event;

        return $this;
    }

    public function getImageEvent(): ?string
    {
        return $this->image_event;
    }

    public function setImageEvent(string $image_event): static
    {
        $this->image_event = $image_event;

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(int $capacite): static
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getNbRestant(): ?int
    {
        return $this->nb_restant;
    }

    public function setNbRestant(int $nb_restant): static
    {
        $this->nb_restant = $nb_restant;

        return $this;
    }

    public function getCreatedAT(): ?\DateTimeInterface
    {
        return $this->created_AT;
    }

    public function setCreatedAT(\DateTimeInterface $created_AT): static
    {
        $this->created_AT = $created_AT;

        return $this;
    }

    public function getUpdatedAT(): ?\DateTimeInterface
    {
        return $this->updated_AT;
    }

    public function setUpdatedAT(\DateTimeInterface $updated_AT): static
    {
        $this->updated_AT = $updated_AT;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setEventId($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getEventId() === $this) {
                $reservation->setEventId(null);
            }
        }

        return $this;
    }
}
