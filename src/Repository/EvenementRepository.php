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

    public function searchEventsAdmin(?string $searchTerm, ?string $location, ?string $date): array
{
    $qb = $this->createQueryBuilder('e');

    if (!empty($searchTerm)) {
        $qb->andWhere('LOWER(e.nomEvent) LIKE LOWER(:search)')
           ->setParameter('search', '%' . strtolower($searchTerm) . '%');
    }

    if (!empty($location)) {
        $qb->andWhere('LOWER(e.lieuEvent) LIKE LOWER(:location)')
           ->setParameter('location', '%' . strtolower($location) . '%');
    }

    if (!empty($date)) {
        $qb->andWhere('e.dateEvent = :date')
           ->setParameter('date', new \DateTime($date));
    }

    return $qb->getQuery()->getResult();
}
public function searchEvents(?string $location, ?string $date): array
{
    $qb = $this->createQueryBuilder('e');

    if (!empty($location)) {
        $qb->andWhere('LOWER(e.lieuEvent) LIKE LOWER(:location)')
           ->setParameter('location', '%' . strtolower($location) . '%');
    }

    if (!empty($date)) {
        $qb->andWhere('e.dateEvent = :date')
           ->setParameter('date', new \DateTime($date));
    }

    // Order by dateEvent in ascending order (closest dates first)
    $qb->orderBy('e.dateEvent', 'ASC');

    return $qb->getQuery()->getResult();
}   
//    /**
//     * @return Evenement[] Returns an array of Evenement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Evenement
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
