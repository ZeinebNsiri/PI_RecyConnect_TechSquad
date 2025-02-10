<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class Base2backOfficeController extends AbstractController
{
    #[Route('/base2back/office', name: 'app_base2back_office')]
    public function index(): Response
    {
        return $this->render('base2back_office/index.html.twig', [
            'controller_name' => 'Base2backOfficeController',
        ]);
    }
}
