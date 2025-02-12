<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


final class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart')]
    public function index(SessionInterface $session, ArticleRepository $articlerepository)
    {
      $panier = $session->get('panier',[]);
      
      //on initialise des variables 
      $data = [];
      $total = 0;

      foreach($panier as $id => $quantite){
      $article = $articlerepository->find($id);

      $data[] = [
        'article' => $article,
        'quantite' => $quantite
      ];
      $total += $article->getPrix() * $quantite; 

    }
   
    return $this->render('cart/index.html.twig', compact('data','total'));
    }
    #[Route('/cartadd/{id}', name: 'cart_add')]
    public function add(Article $articlepanier, SessionInterface $session)
    {

        //recuperer id de l'article
        $id = $articlepanier->getId();

        //recuperer le panier existant 
        $panier = $session->get('panier',[]);

        //on ajoute l'article dans le panier
        //sinon on incremente sa quantite 
        if(empty($panier[$id])){
            $panier[$id] = 1;
        }else{
            $panier[$id]++;
        }

        $session->set('panier', $panier);
       //on redirige vers la page du panier
       return $this -> redirectToRoute('app_cart');


    }
    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Article $articlepanier, SessionInterface $session)
    {

        //recuperer id de l'article
        $id = $articlepanier->getId();

        //recuperer le panier existant 
        $panier = $session->get('panier',[]);

        //on retire l'article du panier
        //sinon on decremente sa quantite 
        if(!empty($panier[$id])){
            if($panier[$id] > 1){
             $panier[$id]--;
        }else{
            unset($panier[$id]);
        }
    }
        $session->set('panier', $panier);
       //on redirige vers la page du panier
       return $this -> redirectToRoute('app_cart');


    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Article $articlepanier, SessionInterface $session)
    {

        //recuperer id de l'article
        $id = $articlepanier->getId();

        //recuperer le panier existant 
        $panier = $session->get('panier',[]);

        if(!empty($panier[$id])){
            unset($panier[$id]);
    }

        $session->set('panier', $panier);
       //on redirige vers la page du panier
       return $this -> redirectToRoute('app_cart');


    }
    #[Route('/empty', name: 'empty')]
    public function empty( SessionInterface $session)
    {
    $session->remove('panier');
    return $this -> redirectToRoute('app_cart');

    }

}
