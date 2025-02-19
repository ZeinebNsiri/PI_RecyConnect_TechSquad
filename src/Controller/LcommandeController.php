<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\ArticleRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

final class LcommandeController extends AbstractController
{
    #[Route('/ajout', name: 'proceder_paiement')]
    public function add(
        SessionInterface $session, 
        ArticleRepository $articleRepository, 
        EntityManagerInterface $entityManager, 
        UtilisateurRepository $UtilisateurRepository, 
        LigneCommandeRepository $ligneCommandeRepository
    ): Response {
        $utilisateur =$this->getUser();
        $panier = $session->get('panier', []);
    
        if (empty($panier)) {
            $this->addFlash('message', 'Votre panier est vide.');
            return $this->redirectToRoute('paiement');
        }
    
        // Création de la commande
        $commande = new Commande();
        $commande->setDateCommande(new \DateTime());
        $commande->setStatut('En attente');
    
        $prixTotal = 0;
    
        // Récupérer toutes les lignes de commande non confirmées pour cet utilisateur
        $lignesCommande = $ligneCommandeRepository->findLignesCommandeSansCommande($utilisateur);
    
        foreach ($lignesCommande as $ligneCommande) {
            $ligneCommande->setCommandeId($commande); // Associer la ligne à la commande
            $ligneCommande->setEtatC('confirmée'); // Changer l'état
            $prixTotal += $ligneCommande->getPrixC(); // Ajouter au prix total
        }
    
        $commande->setPrixTotal($prixTotal);
        
        $entityManager->persist($commande);
        $entityManager->flush();
    
        $this->addFlash('message', 'Commande créée avec succès');
    
        // Vider le panier après validation
        $session->remove('panier');
    
        return $this->redirectToRoute('paiement', ['commandeId' => $commande->getId()]);
    }
    
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Article $articlepanier, SessionInterface $session, EntityManagerInterface $entityManager, UtilisateurRepository $utilisateurRepository)
    {
        $id = $articlepanier->getId();
        $panier = $session->get('panier', []);
    
        $utilisateur = $this->getUser(); // Remplace par l'utilisateur connecté
        $ligneCommandeRepo = $entityManager->getRepository(LigneCommande::class);
        
        $ligneCommande = $ligneCommandeRepo->findOneBy([
            'user_c' => $utilisateur,
            'article_c' => $articlepanier,
        ]);
    
        if ($ligneCommande) {
            $entityManager->remove($ligneCommande);
            $entityManager->flush();
        }
    
        // Supprimer l'article du panier en session
        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }
    
        $session->set('panier', $panier);
    
        return $this->redirectToRoute('app_cart');
    }
    

}
