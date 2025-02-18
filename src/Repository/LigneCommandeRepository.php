<?php

namespace App\Repository;

use App\Entity\LigneCommande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LigneCommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LigneCommande::class);
    }
    public function findLigneCommandeNonConfirmee($utilisateur, $article)
    {
        return $this->createQueryBuilder('lc')
            ->where('lc.user_c = :utilisateur')
            ->andWhere('lc.article_c = :article')
            ->andWhere('lc.etat_c = :non_confirmee')
            ->andWhere('lc.commande_id IS NULL')
            ->setParameter('non_confirmee', 'non confirmée')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('article', $article)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function findLignesCommandeSansCommande($utilisateur)
    {
        return $this->createQueryBuilder('lc')
            ->where('lc.user_c = :utilisateur')
            ->andWhere('lc.commande_id IS NULL')
            ->andWhere('lc.etat_c = :etat')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('etat', 'non confirmée')
            ->getQuery()
            ->getResult();
    }
}
