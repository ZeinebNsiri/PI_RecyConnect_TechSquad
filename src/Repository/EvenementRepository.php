<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * Search events for the admin panel with optional filters.
     *
     * @param string|null $searchTerm
     * @param string|null $location
     * @param string|null $date
     * @return Evenement[]
     */
    public function searchEventsAdmin(?string $searchTerm, ?string $location, ?string $date, ?string $type): array
{
    $qb = $this->createQueryBuilder('e');

    // Filter by search term (event name)
    if (!empty($searchTerm)) {
        $qb->andWhere('LOWER(e.nomEvent) LIKE LOWER(:search)')
           ->setParameter('search', '%' . strtolower($searchTerm) . '%');
    }

    // Filter by location
    if (!empty($location)) {
        $qb->andWhere('LOWER(e.lieuEvent) LIKE LOWER(:location)')
           ->setParameter('location', '%' . strtolower($location) . '%');
    }

    // Filter by date
    if (!empty($date)) {
        $qb->andWhere('e.dateEvent = :date')
           ->setParameter('date', new \DateTime($date));
    }

    // Filter by event type (en ligne or sur site)
    if (!empty($type)) {
        if ($type === 'en ligne') {
            $qb->andWhere('e.lieuEvent = :type')
               ->setParameter('type', 'en ligne');
        } elseif ($type === 'sur site') {
            $qb->andWhere('e.lieuEvent != :type')
               ->setParameter('type', 'en ligne');
        }
    }

    return $qb->getQuery()->getResult();
}

    /**
     * Search events with optional filters.
     *
     * @param string|null $name
     * @param string|null $location
     * @param string|null $date
     * @param string|null $type
     * @return Evenement[]
     */
    public function searchEvents(?string $name, ?string $location, ?string $date, ?string $type): array
{
    $qb = $this->createQueryBuilder('e');

    if ($name) {
        $qb->andWhere('e.nomEvent LIKE :name')
           ->setParameter('name', '%' . $name . '%');
    }

    if ($location) {
        $qb->andWhere('e.lieuEvent LIKE :location')
           ->setParameter('location', '%' . $location . '%');
    }

    if ($date) {
        $qb->andWhere('e.dateEvent = :date')
           ->setParameter('date', $date);
    }

    if ($type) {
        if ($type === 'en ligne') {
            $qb->andWhere('e.lieuEvent = :type')
               ->setParameter('type', 'en ligne');
        } elseif ($type === 'sur site') {
            $qb->andWhere('e.lieuEvent != :type')
               ->setParameter('type', 'en ligne');
        }
    }

    return $qb->getQuery()->getResult();
}

  

    /**
     * Find events by name.
     *
     * @param string $name
     * @return Evenement[]
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.nomEvent LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }
}