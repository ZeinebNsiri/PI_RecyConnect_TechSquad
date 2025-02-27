<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;


/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Search for reservations based on event name, username, and status.
     *
     * @param string|null $eventName
     * @param string|null $username
     * @param string|null $status
     * @return Reservation[]
     */
    public function searchReservations(?string $eventName, ?string $username, ?string $status): array
{
    $qb = $this->createQueryBuilder('r')
        ->leftJoin('r.event', 'e') // Corrected association name
        ->leftJoin('r.user_id', 'u'); // Corrected association name

    if (!empty($eventName)) {
        $qb->andWhere('LOWER(e.nomEvent) LIKE LOWER(:eventName)')
           ->setParameter('eventName', '%' . strtolower($eventName) . '%');
    }

    if (!empty($username)) {
        $qb->andWhere('LOWER(u.nom_user) LIKE LOWER(:username)')
           ->setParameter('username', '%' . strtolower($username) . '%');
    }

    if (!empty($status)) {
        $qb->andWhere('r.status = :status')
           ->setParameter('status', $status);
    }

    return $qb->getQuery()->getResult();
}
public function findMostPopularEvents($limit = 5)
{
    return $this->createQueryBuilder('r')
        ->select('e.id, e.nomEvent, e.dateEvent, e.lieuEvent, COUNT(r.id) AS total_reservations')
        ->join('r.event', 'e')
        ->groupBy('e.id')
        ->orderBy('total_reservations', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}

//    /**
//     * @return Reservation[] Returns an array of Reservation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Reservation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
