<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

final class PaiementController extends AbstractController
{
    #[Route('/paiement/{commandeId}', name: 'paiement')]
    public function index(int $commandeId, CommandeRepository $commandeRepository): Response
    {
        // Récupérer la commande à partir de son ID
        $commande = $commandeRepository->find($commandeId);

        if (!$commande) {
            // Si la commande n'existe pas, afficher un message d'erreur
            $this->addFlash('error', 'Commande non trouvée.');
            return $this->redirectToRoute('app_cart');
        }

        // Passer les informations de la commande à la vue
        return $this->render('paiement/paiement.html.twig', [
            'commande' => $commande
        ]);
    }

    #[Route('/traiter-paiement/{commandeId}', name: 'traiter_paiement', methods: ['POST'])]
    public function traiterPaiement(int $commandeId, Request $request, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la commande
        $commande = $commandeRepository->find($commandeId);

        if (!$commande) {
            $this->addFlash('error', 'Commande non trouvée.');
            return $this->redirectToRoute('app_cart');
        }

        // Récupérer le mode de paiement depuis le formulaire
        $modePaiement = $request->request->get('mode_paiement');

        if ($modePaiement === 'livraison') {
            $commande->setStatut('Payé à la livraison');
        } elseif ($modePaiement === 'visa') {
            $commande->setStatut('Payé par VISA');
        }

        // Sauvegarder les modifications dans la base de données
        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Votre paiement a été effectué avec succès.');

        // Rediriger l'utilisateur vers la page du panier
        return $this->redirectToRoute('app_articles');
    }
}
