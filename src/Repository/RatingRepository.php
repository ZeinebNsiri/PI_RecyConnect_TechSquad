<?php

namespace App\Repository;

use App\Entity\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rating>
 */
class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

  

    public function getRatingSumByCategory(): array
    {
        return $this->createQueryBuilder('r')
            ->select('cat.nomCategorie AS category, SUM(r.note) AS ratingSum')
            ->join('r.cours', 'c')
            ->join('c.categorieC', 'cat')
            ->groupBy('cat.id')
            ->orderBy('ratingSum', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

}
