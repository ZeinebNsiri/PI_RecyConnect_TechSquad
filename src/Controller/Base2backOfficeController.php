<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategorieArticleRepository;
use App\Repository\CoursRepository;
use App\Repository\RatingRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class Base2backOfficeController extends AbstractController
{
    #[Route('/dashboard', name: 'app_base2back_office')]
    public function dashboard(
        ArticleRepository $articleRepository,
        CategorieArticleRepository $categorieRepository,
        CoursRepository $coursRepository,
        RatingRepository $ratingRepository,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response 
    {
        /**
         * ----------------------------------------------------------------
         *  1) Statistiques sur les Articles
         * ----------------------------------------------------------------
         */
        $categories = $categorieRepository->findAll();
        $categoryData = [];
        foreach ($categories as $categorie) {
            $categoryData[] = [
                'nom'   => $categorie->getNomCategorie(),
                'count' => $articleRepository->countArticlesInCategory($categorie->getId()),
            ];
        }

        $articles = $articleRepository->findAll();
        $articleData = [];
        foreach ($articles as $article) {
            $articleData[] = [
                'nom'      => $article->getNomArticle(),
                'quantite' => $article->getQuantiteArticle(),
            ];
        }

        /**
         * ----------------------------------------------------------------
         *  2) Statistiques sur les Cours / Workshops
         * ----------------------------------------------------------------
         */
        $statsCategory            = $coursRepository->countByCategory();
        $statsRatingSumByCategory = $ratingRepository->getRatingSumByCategory();
        $averageByWorkshop        = $ratingRepository->getAverageRatingByWorkshop();

        /**
         * ----------------------------------------------------------------
         *  3) Statistiques Ventes (Commandes)
         * ----------------------------------------------------------------
         */
        // A) Total des commandes confirmées aujourd'hui
        $todayOrdersTotal = $this->getTodayOrdersTotal($entityManager);

        // B) Nombre de commandes payées
        $paidOrdersCount = $this->getPaidOrdersCount($entityManager);

        /**
         * ----------------------------------------------------------------
         *  4) Statistiques des Utilisateurs
         * ----------------------------------------------------------------
         *  (Mimicking your getStats method, but we'll pass data directly)
         */
        $users = $utilisateurRepository->findAll();
        // Initialisation des compteurs
        $rolesCounts = [
            'Professionnels' => 0,
            'Particuliers'   => 0
        ];
        $statusCounts = [
            'Activés'    => 0,
            'Désactivés' => 0
        ];

        foreach ($users as $user) {
            $roles = $user->getRoles();
            if (in_array('ROLE_PROFESSIONNEL', $roles)) {
                $rolesCounts['Professionnels']++;
                // Statut
                if ($user->isStatus()) {
                    $statusCounts['Activés']++;
                } else {
                    $statusCounts['Désactivés']++;
                }
            } elseif (in_array('ROLE_USER', $roles)) {
                $rolesCounts['Particuliers']++;
                // Statut
                if ($user->isStatus()) {
                    $statusCounts['Activés']++;
                } else {
                    $statusCounts['Désactivés']++;
                }
            }
        }

        // We'll pass these stats as arrays directly:
        $userStats = [
            'roles'  => $rolesCounts,
            'status' => $statusCounts,
        ];

        /**
         * ----------------------------------------------------------------
         *  5) Statistiques des Événements
         * ----------------------------------------------------------------
         * Below, adapt to however you actually get $popularEvents and $capacityUtilization.
         * For demonstration, we pretend you have them from some repository calls.
         */
        $popularEvents = [];         // e.g.  $eventRepository->findPopularEvents();
        $capacityUtilization = [];   // e.g.  $eventRepository->getCapacityUtilization();

        // Send everything to ONE Twig template
        return $this->render('base2back_office/index.html.twig', [
            // 1) Articles
            'categoryData' => json_encode($categoryData),
            'articleData'  => json_encode($articleData),

            // 2) Cours/Workshops
            'statsCategory'            => $statsCategory,
            'statsRatingSumByCategory' => $statsRatingSumByCategory,
            'averageByWorkshop'        => $averageByWorkshop,

            // 3) Ventes
            'todayOrdersTotal' => $todayOrdersTotal,
            'paidOrdersCount'  => $paidOrdersCount,

            // 4) Utilisateurs
            'userStats' => $userStats,

            // 5) Événements
            'popularEvents'       => $popularEvents,
            'capacityUtilization' => $capacityUtilization,
        ]);
    }

    /**
     * Méthode privée pour récupérer le nombre de commandes payées
     */
    private function getPaidOrdersCount(EntityManagerInterface $entityManager): int
    {
        $query = $entityManager->createQuery(
            'SELECT COUNT(c.id) 
             FROM App\Entity\Commande c
             WHERE c.statut IN (:statuts)'
        )->setParameter('statuts', ['Payé à la livraison', 'Payé par VISA']);

        return (int) $query->getSingleScalarResult();
    }

    /**
     * Méthode privée pour récupérer le total des commandes d'aujourd'hui
     */
    private function getTodayOrdersTotal(EntityManagerInterface $entityManager): float
    {
        $today      = new \DateTime('today'); // Date du jour sans l'heure
        $startOfDay = $today->format('Y-m-d 00:00:00');
        $endOfDay   = $today->format('Y-m-d 23:59:59');

        $sql = '
            SELECT SUM(c.prix_total) 
            FROM commande c 
            WHERE c.statut = :statut
              AND c.date_commande BETWEEN :startOfDay AND :endOfDay
        ';

        $result = $entityManager->getConnection()->executeQuery($sql, [
            'statut'     => 'Payé à la livraison',
            'startOfDay' => $startOfDay,
            'endOfDay'   => $endOfDay
        ]);

        $total = $result->fetchOne();
        return $total ? (float) $total : 0;
    }
}