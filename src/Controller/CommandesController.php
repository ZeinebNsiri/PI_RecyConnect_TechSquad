<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Utilisateur;
use App\Repository\CommandeRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
        // Récupérer l'utilisateur connecté
        $user = $this->getUser(); // Utiliser $this->getUser() pour obtenir l'utilisateur connecté
    
        // Récupérer les lignes de commande associées à l'utilisateur
        $lignesCommande = $entityManager->getRepository(LigneCommande::class)->findBy(['user_c' => $user]);
    
        // Récupérer les commandes associées aux lignes de commande (en éliminant les doublons)
        $commandes = [];
        foreach ($lignesCommande as $ligne) {
            $commande = $ligne->getCommandeId();
            if (!in_array($commande, $commandes, true)) {
                $commandes[] = $commande;
            }
        }
    
        // Passer les commandes à la vue Twig
        return $this->render('mes_commandes.html.twig', [
            'commande' => $commandes, // Corriger ici pour passer 'commandes' au lieu de 'commande'
        ]);
    }
    
    

    #[Route('/pdf/{id}', name: 'app_facture_pdf')]
    public function generatePdf(
        CommandeRepository $commandeRepo,
        LigneCommandeRepository $ligneCommandeRepo,
        UtilisateurRepository $userRepo,
        Security $security,
        $id
    ): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
    
        // Si l'utilisateur n'est pas connecté, afficher un message
        if (!$user) {
            throw new \Exception("Utilisateur non connecté.");
        }
    
        // Récupérer la commande en fonction de l'ID de la commande
        $commande = $commandeRepo->find($id);
    
        if (!$commande) {
            throw new \Exception("Commande non trouvée.");
        }
    
        // Vérifier que la commande appartient bien à l'utilisateur connecté
        // On vérifie si l'utilisateur est présent dans les lignes de commande
        $lignesCommande = $ligneCommandeRepo->findBy(['commande_id' => $commande]);
        $userIsOwner = false;
    
        foreach ($lignesCommande as $ligne) {
            if ($ligne->getUserC() === $user) {
                $userIsOwner = true;
                break;
            }
        }
    
        if (!$userIsOwner) {
            throw new \Exception("Vous n'avez pas accès à cette commande.");
        }
    
        // Encoder le logo en Base64
        $logoPath = $this->getParameter('kernel.project_dir') . '/assets/backOffice/assets/img/mainlogo.png';
    
        if (!file_exists($logoPath)) {
            throw new \Exception("Le fichier du logo est introuvable à l'emplacement : " . $logoPath);
        }
    
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;
    
        // Générer le contenu du PDF avec Twig
        $html = $this->renderView('mes_commandes.html.twig', [
            'commande' => $commande,
            'lignesCommande' => $lignesCommande,
            'utilisateur' => $user, // Passer l'utilisateur pour afficher son nom et prénom
            'logoSrc' => $logoSrc
        ]);
    
        // Générer le PDF avec Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        // Retourner le PDF comme réponse
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="facture_' . $commande->getId() . '.pdf"',
        ]);
    }
    
  }