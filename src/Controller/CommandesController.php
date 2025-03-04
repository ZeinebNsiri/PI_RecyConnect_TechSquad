<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\CommandeRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommandesController extends AbstractController
{
    #[Route('/commandes', name: 'liste_commandes')]
    public function listeCommandes(EntityManagerInterface $entityManager): Response
    {
        $commandes = $entityManager->getRepository(Commande::class)->findAll();
        
        return $this->render('commandeadmin.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/mes-commandes', name: 'mes_commandes')]
    public function mesCommandes(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour voir vos commandes.");
        }

        $lignesCommande = $entityManager->getRepository(LigneCommande::class)->findBy(['user_c' => $user]);
        
        $commandes = [];
        foreach ($lignesCommande as $ligne) {
            $commande = $ligne->getCommandeId();
            if (!in_array($commande, $commandes, true)) {
                $commandes[] = $commande;
            }
        }

        return $this->render('mes_commandes.html.twig', [
            'commande' => $commandes,
        ]);
    }
    #[Route('/pdf/{id}', name: 'app_facture_pdf')]
public function generatePdf(
    CommandeRepository $commandeRepo,
    LigneCommandeRepository $ligneCommandeRepo,
    Security $security,
    $id
): Response {
    $user = $security->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException("Utilisateur non connecté.");
    }

    $commande = $commandeRepo->find($id);
    if (!$commande) {
        throw $this->createNotFoundException("Commande non trouvée.");
    }

    // Récupérer les lignes de commande liées à cette commande
    $lignesCommande = $ligneCommandeRepo->findBy(['commande_id' => $commande]);

    $userIsOwner = false;
    foreach ($lignesCommande as $ligne) {
        if ($ligne->getUserC() === $user) {
            $userIsOwner = true;
            break;
        }
    }

    if (!$userIsOwner) {
        throw $this->createAccessDeniedException("Vous n'avez pas accès à cette commande.");
    }

    // Calcul du total
    $total = 0;
    foreach ($lignesCommande as $ligne) {
        $total += $ligne->getPrixC() * $ligne->getQuantiteC();
    }

    // Rendu de la vue Twig avec les données nécessaires
    $html = $this->renderView('/pdf/commande_pdf.html.twig', [
        'commande' => $commande,
        'lignesCommande' => $lignesCommande,
        'utilisateur' => $user,
        'total' => $total, // Envoi du total calculé
    ]);

    // Création du PDF avec Dompdf
    $pdfOptions = new Options();
    $pdfOptions->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($pdfOptions);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Retourner le PDF généré
    return new Response($dompdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="facture_' . $commande->getId() . '.pdf"'
    ]);
}



}