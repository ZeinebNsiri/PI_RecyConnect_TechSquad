<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return Utilisateur[] Returns an array of Utilisateur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

       public function findusers(): array
       {
           return $this->createQueryBuilder('u')
               ->Where('u.roles LIKE :role1')
               ->orWhere('u.roles LIKE :role2')
               ->setParameter('role1', '%ROLE_USER%')
               ->setParameter('role2', '%ROLE_PROFESSIONNEL%')
               ->getQuery()
               ->getResult()
           ;
       }

       public function findByRole(string $role): array
       {
        return $this->createQueryBuilder('u')
        ->andWhere('u.roles LIKE :role')
        ->setParameter('role', '%"'.$role.'"%')
        ->getQuery()
        ->getResult();
       }

       public function findBystatus(bool $status): array
        {
            return $this->createQueryBuilder('u')
                ->Where('u.roles LIKE :role1')
                ->orWhere('u.roles LIKE :role2')
                ->andWhere('u.status = :status')
                ->setParameter('status', $status)
                ->setParameter('role1', '%ROLE_USER%')
                ->setParameter('role2', '%ROLE_PROFESSIONNEL%')
                ->getQuery()
                ->getResult();
        }
        public function searchUsers(?string $email, ?string $numTel, ?string $role): array
        {
            $qb = $this->createQueryBuilder('u');

            if ($email) {
                $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', "%$email%");
            }

            

            if ($numTel) {
                $qb->andWhere('u.numTel LIKE :numTel')
                ->setParameter('numTel', "%$numTel%");
            }

           

            if ($role) {
                $qb->andWhere('u.roles LIKE :role')
                   ->setParameter('role', "%$role%");
            }
            

           

            return $qb->getQuery()->getResult();
        }
        public function countByRole(): array
        {
            return $this->createQueryBuilder('u')
                ->select('COUNT(u.id) as count, u.roles')
                ->groupBy('u.roles')
                ->getQuery()
                ->getResult();
        }

        public function countByStatus(): array
        {
            return $this->createQueryBuilder('u')
                ->select('COUNT(u.id) as count, u.status')
                ->groupBy('u.status')
                ->getQuery()
                ->getResult();
        }

}
