<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\ArticleRepository;
use App\Repository\CommandeRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class CartController extends AbstractController
{
    #[Route('/test', name: 'app_cart')]
    public function index(SessionInterface $session, ArticleRepository $articlerepository)
    {
        $panier = $session->get('panier', []);
        
        $data = [];
        $total = 0;

        foreach ($panier as $id => $quantite) {
            $article = $articlerepository->find($id);
            if ($article) {
                $data[] = [
                    'article' => $article,
                    'quantite' => $quantite
                ];
                $total += $article->getPrix() * $quantite;
            }
        }
        
        return $this->render('cart/index.html.twig', compact('data', 'total'));
    }

    #[Route('/cartadd/{id}', name: 'cart_add')]
    public function add(
        Article $articlepanier, 
        SessionInterface $session, 
        EntityManagerInterface $entityManager, 
        UtilisateurRepository $utilisateurRepository, 
        LigneCommandeRepository $ligneCommandeRepository
    ) {
        $id = $articlepanier->getId();
        $panier = $session->get('panier', []);
    
        $utilisateur = $this->getUser(); // Remplace par l'utilisateur connecté
    
        // Récupérer la quantité disponible
        $quantiteDisponible = $articlepanier->getQuantiteArticle(); 
        
        // Récupérer la ligne de commande existante
        $ligneCommande = $ligneCommandeRepository->findLigneCommandeNonConfirmee($utilisateur, $articlepanier);
    
        if ($ligneCommande) {
            $nouvelleQuantite = $ligneCommande->getQuantiteC() + 1;
    
            // Vérifier si on dépasse la quantité disponible
            if ($nouvelleQuantite > $quantiteDisponible) {
                $this->addFlash('error', "Stock insuffisant. Quantité maximale : $quantiteDisponible.");
                return $this->redirectToRoute('app_cart');
            }
    
            // Mise à jour de la quantité
            $ligneCommande->setQuantiteC($nouvelleQuantite);
            $ligneCommande->setPrixC($articlepanier->getPrix() * $nouvelleQuantite);
        } else {
            if ($quantiteDisponible < 1) {
                $this->addFlash('error', "Stock insuffisant.");
                return $this->redirectToRoute('app_cart');
            }
    
            // Création d'une nouvelle ligne de commande
            $ligneCommande = new LigneCommande();
            $ligneCommande->setUserC($utilisateur);
            $ligneCommande->setArticleC($articlepanier);
            $ligneCommande->setQuantiteC(1);
            $ligneCommande->setPrixC($articlepanier->getPrix());
            $ligneCommande->setEtatC("non confirmée");
    
            $entityManager->persist($ligneCommande);
        }
    
        $entityManager->flush();
    
        // Mettre à jour le panier dans la session
        $panier[$id] = $ligneCommande->getQuantiteC();
        $session->set('panier', $panier);
    
        return $this->redirectToRoute('app_cart');
    }
    
    

    #[Route('/empty', name: 'empty')]
    public function empty(SessionInterface $session)
    {
        $session->remove('panier');
        return $this->redirectToRoute('app_cart');
    }
}
