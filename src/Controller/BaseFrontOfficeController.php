<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BaseFrontOfficeController extends AbstractController
{
    #[Route('/base/front/office', name: 'app_base_front_office')]
    public function index(): Response
    {
        return $this->render('base_front_office/index.html.twig', [
            'controller_name' => 'BaseFrontOfficeController',
        ]);
    }

    #[Route('/base/front/office/cnx', name: 'app_base_front_office_cnx')]
    public function cnx(): Response
    {
        return $this->render('base_front_office/cnx.html.twig', [
            'controller_name' => 'BaseFrontOfficeController',
        ]);
    }
    #[Route('/home', name: 'home')]

    public function home( ): Response
    {
        return $this->render('home.html.twig', [
            'welcome_message' => 'Bienvenue sur RecyConnect!',
        ]);
    }
    #[Route('/homecnx', name: 'homecnx')]

    public function homecnx( ): Response
    {
        return $this->render('homecnx.html.twig', [
            'welcome_message' => 'Bienvenue sur RecyConnect!',
        ]);
    }

}
