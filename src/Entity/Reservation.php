<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $nb_places = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evenement $event_id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user_id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_atRes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated_atRes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nb_places;
    }

    public function setNbPlaces(int $nb_places): static
    {
        $this->nb_places = $nb_places;

        return $this;
    }

    public function getEventId(): ?Evenement
    {
        return $this->event_id;
    }

    public function setEventId(?Evenement $event_id): static
    {
        $this->event_id = $event_id;

        return $this;
    }

    public function getUserId(): ?Utilisateur
    {
        return $this->user_id;
    }

    public function setUserId(?Utilisateur $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getCreatedAtRes(): ?\DateTimeInterface
    {
        return $this->created_atRes;
    }

    public function setCreatedAtRes(\DateTimeInterface $created_atRes): static
    {
        $this->created_atRes = $created_atRes;

        return $this;
    }

    public function getUpdatedAtRes(): ?\DateTimeInterface
    {
        return $this->updated_atRes;
    }

    public function setUpdatedAtRes(?\DateTimeInterface $updated_atRes): static
    {
        $this->updated_atRes = $updated_atRes;

        return $this;
    }
}
