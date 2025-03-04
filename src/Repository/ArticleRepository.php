<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

//    /**
//     * @return Article[] Returns an array of Article objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findByCategory($categoryId)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.categorie = :cat')
            ->setParameter('cat', $categoryId)
            ->getQuery()
            ->getResult();
    }
    public function findByCategory2(int $category)
    {
        return $this->createQueryBuilder('a')
            ->join('a.categorie', 'c')
            ->andWhere('c.id = :cat') 
            ->setParameter('cat', $category)
            ->getQuery()
            ->getResult();
    }
    
    public function findByCategorymine($categoryId,$user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.categorie = :cat')
            ->andWhere('a.utilisateur =:id')
            ->setParameter('cat', $categoryId)
            ->setParameter('id', $user)
            ->getQuery()
            ->getResult();
    }

    public function findmine($user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.utilisateur = :id')
            ->setParameter('id', $user)
            ->getQuery()
            ->getResult();
    }

    public function countArticlesInCategory($categoryId)
    {
    return $this->createQueryBuilder('a')
        ->select('COUNT(a.id) as article_count')
        ->andWhere('a.categorie = :cat') 
        ->setParameter('cat', $categoryId)
        ->getQuery()
        ->getSingleScalarResult();
    }

    public function searchBymulticritaire(?string $articleNom, ?string $proprietaireNom): array
    {
        $query = $this->createQueryBuilder('a')
            ->leftJoin('a.utilisateur', 'u')
            ->addSelect('u');
    
        if (!empty($articleNom)) {
            $query->andWhere('a.nom_article LIKE :articleNom') 
                  ->setParameter('articleNom', '%'.$articleNom.'%');
        }
    
        if (!empty($proprietaireNom)) {
            $query->andWhere('u.nom_user LIKE :proprietaireNom OR u.prenom LIKE :proprietaireNom') 
                  ->setParameter('proprietaireNom', '%'.$proprietaireNom.'%');
        }
    
        return $query->getQuery()->getResult();
    }
    
    


}
