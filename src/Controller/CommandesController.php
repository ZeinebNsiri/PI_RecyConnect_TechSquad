<?php

namespace App\Controller;

use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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


}
