<?php

namespace App\Repository;

use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

    /**
     * Récupère la liste (unique) des catégories à partir de l’association `categorieC`.
     */
    public function findUniqueCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.categorieC', 'cat')  // Jointure avec l'entité liée
            ->select('DISTINCT cat.nomCategorie AS nomCategorie') 
            ->orderBy('cat.nomCategorie', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les cours d’une catégorie donnée.
     */
    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.categorieC', 'cat')
            ->where('cat.nomCategorie = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }
}
