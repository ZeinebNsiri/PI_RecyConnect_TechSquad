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

    /**
     * 
     *
     * @param string|null 
     * @param string|null 
     * @param string|null 
     */
    public function findByAllFilters(?string $category, ?string $searchTitle, ?string $videoFilter): array
    {
        $qb = $this->createQueryBuilder('c')
                   ->leftJoin('c.categorieC', 'cat')
                   ->addSelect('cat');

        // Filtrer par catégorie si précisé
        if ($category) {
            $qb->andWhere('cat.nomCategorie = :category')
               ->setParameter('category', $category);
        }

        // Filtrer par titre
        if ($searchTitle) {
            $qb->andWhere('c.titreCours LIKE :searchTitle')
               ->setParameter('searchTitle', '%'.$searchTitle.'%');
        }

        // Filtrer par présence ou absence de vidéo
        if ($videoFilter === 'yes') {
          
            $qb->andWhere('c.video IS NOT NULL AND c.video != \'\'');
        } elseif ($videoFilter === 'no') {
            
            $qb->andWhere('c.video IS NULL OR c.video = \'\'');
        }

        return $qb->getQuery()->getResult();
    }


    public function countByCategory(): array
    {
        // Récupérer la catégorie (nomCategorie) + le count
        return $this->createQueryBuilder('c')
            ->select('cat.nomCategorie AS category, COUNT(c.id) AS total') // Alias total
            ->leftJoin('c.categorieC', 'cat')
            ->groupBy('cat.nomCategorie')
            ->orderBy('cat.nomCategorie', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
