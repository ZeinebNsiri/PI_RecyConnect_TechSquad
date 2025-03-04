<?php 

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class SalescontrollerController extends AbstractController
{
    private CommandeRepository $commandeRepository;

    public function __construct(CommandeRepository $commandeRepository)
    {
        $this->commandeRepository = $commandeRepository;
    }

    #[Route('/salescontroller', name: 'app_salescontroller')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le total des commandes confirmées aujourd'hui
        $todayOrdersTotal = $this->getTodayOrdersTotal($entityManager);
    
        // Récupérer le nombre de commandes payées
        $paidOrdersCount = $this->getPaidOrdersCount($entityManager);
    
        // Passer les valeurs au template
        return $this->render('commandes/sales.html.twig', [
            'todayOrdersTotal' => $todayOrdersTotal,  // Total des commandes confirmées aujourd'hui
            'paidOrdersCount' => $paidOrdersCount,    // Nombre de commandes payées
        ]);
    }
    

    // Méthode pour récupérer le nombre de commandes d'aujourd'hui
   // Méthode pour récupérer le nombre de commandes avec les statuts "Payé à la livraison" ou "Payé par VISA"
   private function getPaidOrdersCount(EntityManagerInterface $entityManager): int
   {
       // Créer la requête DQL pour récupérer le nombre de commandes avec les statuts spécifiques
       $query = $entityManager->createQuery(
           'SELECT COUNT(c.id) 
            FROM App\Entity\Commande c 
            WHERE c.statut IN (:statuts)'
       )->setParameter('statuts', ['Payé à la livraison', 'Payé par VISA']);
   
       // Exécuter la requête et retourner le résultat
       return (int) $query->getSingleScalarResult();
   }
   

   private function getTodayOrdersTotal(EntityManagerInterface $entityManager): float
{
    // Créer la date actuelle (sans l'heure)
    $today = new \DateTime('today');  // Date du jour sans l'heure
    $startOfDay = $today->format('Y-m-d 00:00:00');  // Début de la journée
    $endOfDay = $today->format('Y-m-d 23:59:59');  // Fin de la journée

    // Créer la requête SQL native pour récupérer le prix total des commandes d'aujourd'hui
    $sql = '
        SELECT SUM(c.prix_total) 
        FROM commande c 
        WHERE c.statut = :statut
        AND c.date_commande BETWEEN :startOfDay AND :endOfDay
    ';

    // Exécuter la requête avec les paramètres
    $result = $entityManager->getConnection()->executeQuery($sql, [
        'statut' => 'Payé à la livraison',
        'startOfDay' => $startOfDay,
        'endOfDay' => $endOfDay
    ]);

    // Retourne le résultat ou 0 si aucune commande
    $total = $result->fetchOne();
    return $total ? (float) $total : 0;
}

   
   




}
